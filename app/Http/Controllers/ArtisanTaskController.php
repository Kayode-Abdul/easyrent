<?php

namespace App\Http\Controllers;

use App\Models\ArtisanTask;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArtisanTaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store a new artisan task (Landlord/Admin action)
     */
    public function store(Request $request)
    {
        $request->validate([
            'complaint_id' => 'required|exists:complaints,id',
            'budget_min' => 'required|numeric|min:0',
            'budget_max' => 'required|numeric|min:0|gte:budget_min',
            'duration' => 'required|string',
            'description' => 'nullable|string',
            'request_setoff' => 'nullable|boolean',
        ]);

        $complaint = Complaint::findOrFail($request->complaint_id);

        // Authorization: Only landlord of the property, the tenant of the property, or admin can post tasks
        $user = Auth::user();
        if ($user->user_id != $complaint->landlord_id && $user->user_id != $complaint->tenant_id && !$user->admin) {
            return back()->with('error', 'Unauthorized action.');
        }

        // Check if task already exists
        if ($complaint->artisanTask) {
            return back()->with('error', 'A task already exists for this complaint.');
        }

        $task = ArtisanTask::create([
            'complaint_id' => $complaint->id,
            'landlord_id' => $complaint->landlord_id,
            'tenant_id' => $user->isTenant() ? $user->user_id : null,
            'budget_min' => $request->budget_min,
            'budget_max' => $request->budget_max,
            'duration' => $request->duration,
            'description' => $request->description ?? $complaint->description,
            'status' => 'open',
            'request_setoff' => $request->filled('request_setoff') ? $request->request_setoff : false,
        ]);

        // Log activity or add comment to complaint
        $complaint->addComment(Auth::user(), "Artisan task posted to marketplace with budget ₦" . number_format($request->budget_min) . " - ₦" . number_format($request->budget_max) . ".");

        return back()->with('success', 'Task posted successfully to the artisan marketplace.');
    }

    /**
     * Show task details
     */
    public function show(ArtisanTask $task)
    {
        $task->load(['complaint', 'landlord', 'tenant', 'bids.artisan', 'verificationCode']);
        return view('artisan.tasks.show', compact('task'));
    }

    /**
     * Artisan Marketplace
     */
    public function market()
    {
        $tasks = ArtisanTask::where('status', 'open')
            ->with(['complaint.category', 'landlord'])
            ->latest()
            ->paginate(15);

        return view('artisan.tasks.market', compact('tasks'));
    }

    /**
     * Artisan Dashboard
     */
    public function artisanDashboard()
    {
        $user = Auth::user();

        if (!$user->isArtisan()) {
            return redirect()->route('dashboard')->with('error', 'Access denied. For artisans only.');
        }

        $myBids = $user->artisanBids()->with('task.complaint')->latest()->get();
        $relevantTasks = ArtisanTask::where('status', 'open')
            ->latest()
            ->take(5)
            ->get();

        return view('artisan.dashboard', compact('myBids', 'relevantTasks'));
    }

    /**
     * Place a bid on a task
     */
    public function placeBid(Request $request, ArtisanTask $task)
    {
        if (!Auth::user()->isArtisan()) {
            return back()->with('error', 'Only artisans can place bids.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'duration' => 'required|string',
            'proposal' => 'required|string|min:20',
        ]);

        // Check if already bid
        if ($task->bids()->where('artisan_id', Auth::user()->user_id)->exists()) {
            return back()->with('error', 'You have already placed a bid on this task.');
        }

        $task->bids()->create([
            'artisan_id' => Auth::user()->user_id,
            'amount' => $request->amount,
            'duration' => $request->duration,
            'proposal' => $request->proposal,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Your bid has been submitted successfully.');
    }

    /**
     * Accept an artisan's bid
     */
    public function acceptBid(\App\Models\ArtisanBid $bid)
    {
        $task = $bid->task;

        // Authorization
        $user = Auth::user();
        if ($user->user_id != $task->landlord_id && $user->user_id != $task->tenant_id && !$user->admin) {
            return back()->with('error', 'Unauthorized action.');
        }

        // Update bid status
        $bid->update(['status' => 'accepted']);

        // Reject other bids
        $task->bids()->where('id', '!=', $bid->id)->update(['status' => 'rejected']);

        // Update task status
        $task->update(['status' => 'assigned']);

        // Generate Verification Code
        $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6)); // E.g., A4B9C2

        \App\Models\ArtisanVerificationCode::create([
            'task_id' => $task->id,
            'code' => $code,
            'landlord_id' => $task->landlord_id,
            'tenant_id' => $task->tenant_id,
            'artisan_id' => $bid->artisan_id,
            'expires_at' => now()->addDays(7), // Good for 7 days
        ]);

        // Notify complaint system
        $task->complaint->addComment(Auth::user(), "Artisan bid from {$bid->artisan->first_name} for ₦" . number_format($bid->amount) . " has been accepted.");

        return back()->with('success', 'Bid accepted. The artisan has been notified.');
    }

    /**
     * Mark a task as completed
     */
    public function completeTask(Request $request, ArtisanTask $task)
    {
        $user = Auth::user();
        if ($user->user_id != $task->landlord_id && $user->user_id != $task->tenant_id && !$user->admin) {
            return back()->with('error', 'Unauthorized action.');
        }

        if ($task->status !== 'assigned') {
            return back()->with('error', 'Only assigned tasks can be marked as completed.');
        }

        $task->update(['status' => 'completed']);
        $acceptedBid = $task->bids()->where('status', 'accepted')->first();

        // Rent Set-off Logic
        if ($task->request_setoff && $task->tenant_id && $acceptedBid) {
            $complaint = $task->complaint;

            \App\Models\Payment::create([
                'transaction_id' => 'SET-OFF-' . uniqid(),
                'tenant_id' => $task->tenant_id,
                'landlord_id' => $task->landlord_id,
                'apartment_id' => $complaint->apartment_id,
                'amount' => $acceptedBid->amount,
                'duration' => '0',
                'status' => 'success',
                'payment_method' => 'rent_setoff',
                'payment_reference' => 'Task ID ' . $task->id,
                'paid_at' => now(),
            ]);

            $task->complaint->addComment($user, "Rent set-off of ₦" . number_format($acceptedBid->amount) . " has been recorded.");
        }

        $task->complaint->addComment($user, "Artisan task marked as completed.");

        return back()->with('success', 'Task marked as completed successfully.');
    }
}