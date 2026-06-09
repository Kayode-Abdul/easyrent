<?php

namespace App\Http\Controllers;

use App\Models\ArtisanTask;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\ArtisanAssignedMail;

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
        $complaint->addComment(Auth::user(), "Artisan task posted to marketplace with budget " . format_money($request->budget_min, $complaint->apartment->currency) . " - " . format_money($request->budget_max, $complaint->apartment->currency) . ".");

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
        $user = Auth::user();

        $tasksQuery = ArtisanTask::whereIn('status', ['open', 'assigned'])
            ->whereHas('complaint.apartment.property', function($query) use ($user) {
                $query->where(function($q) use ($user) {
                    if ($user->state) {
                        $q->where('state', $user->state);
                    }
                    if ($user->city) {
                        $q->orWhere('lga', $user->city);
                    }
                });
            });

        // Float tasks that match the artisan's primary category to the top
        if ($user->artisan_category_id) {
            $tasksQuery->orderByRaw('CASE WHEN exists (
                select 1 from complaints where complaints.id = artisan_tasks.complaint_id and complaints.category_id = ?
            ) THEN 0 ELSE 1 END', [$user->artisan_category_id]);
        }

        $tasks = $tasksQuery->with(['complaint.category', 'landlord'])
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

        $myBids = $user->artisanBids()->with('task.complaint.category')->latest()->get();
        
        $stats = [
            'total_bids' => $myBids->count(),
            'pending_bids' => $myBids->where('status', 'pending')->count(),
            'accepted_bids' => $myBids->where('status', 'accepted')->count(),
            'completed_tasks' => ArtisanTask::where('status', 'completed')
                ->whereIn('id', $myBids->where('status', 'accepted')->pluck('task_id'))
                ->count(),
        ];

        // Tasks in artisan's category
        $categoryTasks = ArtisanTask::where('status', 'open')
            ->whereHas('complaint', function($q) use ($user) {
                if ($user->artisan_category_id) {
                    $q->where('category_id', $user->artisan_category_id);
                }
            })
            ->with(['complaint.category', 'landlord'])
            ->latest()
            ->take(5)
            ->get();

        $relevantTasks = ArtisanTask::where('status', 'open')
            ->whereNotIn('id', $categoryTasks->pluck('id'))
            ->with(['complaint.category', 'landlord'])
            ->latest()
            ->take(5)
            ->get();

        return view('artisan.dashboard', compact('myBids', 'categoryTasks', 'relevantTasks', 'stats'));
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
            'is_read' => false,
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

        // Calculate costs
        $platform_fee = $bid->amount * 0.02;
        $gateway_fee = $bid->amount * 0.015;
        $total_amount_paid = $bid->amount + $platform_fee + $gateway_fee;

        // Update task with payment pending
        $payment_reference = 'ARTISAN-' . strtoupper(uniqid());
        
        $task->update([
            'payment_status' => 'pending',
            'payment_reference' => $payment_reference,
            'platform_fee' => $platform_fee,
            'gateway_fee' => $gateway_fee,
            'total_amount_paid' => $total_amount_paid
        ]);

        // Send to Paystack
        $data = [
            'amount' => $total_amount_paid * 100, // Paystack is in kobo
            'email' => $user->email,
            'reference' => $payment_reference,
            'callback_url' => route('artisan.payment.callback'),
            'metadata' => [
                'type' => 'artisan_task',
                'task_id' => $task->id,
                'bid_id' => $bid->id,
            ]
        ];
        
        try {
            return \Unicodeveloper\Paystack\Facades\Paystack::getAuthorizationUrl($data)->redirectNow();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Paystack Error: " . $e->getMessage());
            return back()->with('error', 'The paystack token has expired or an error occurred. Please refresh the page and try again.');
        }
    }

    /**
     * Handle payment callback from Paystack
     */
    public function paymentCallback(Request $request)
    {
        try {
            $paymentDetails = \Unicodeveloper\Paystack\Facades\Paystack::getPaymentData();

            if ($paymentDetails['status']) {
                $metadata = $paymentDetails['data']['metadata'];
                if(isset($metadata['type']) && $metadata['type'] === 'artisan_task') {
                    $task = ArtisanTask::find($metadata['task_id']);
                    $bid = \App\Models\ArtisanBid::find($metadata['bid_id']);

                    if ($task && $bid && $task->payment_status === 'pending') {
                        // Update task status
                        $task->update([
                            'payment_status' => 'escrowed',
                            'status' => 'assigned'
                        ]);

                        // Update bid status
                        $bid->update([
                            'status' => 'accepted',
                            'is_read' => false
                        ]);

                        // Reject other bids
                        $task->bids()->where('id', '!=', $bid->id)->update(['status' => 'rejected']);

                        // Generate Verification Code
                        $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

                        \App\Models\ArtisanVerificationCode::create([
                            'task_id' => $task->id,
                            'code' => $code,
                            'landlord_id' => $task->landlord_id,
                            'tenant_id' => $task->tenant_id,
                            'artisan_id' => $bid->artisan_id,
                            'expires_at' => now()->addDays(7),
                        ]);

                        $task->complaint->addComment(Auth::user(), "Artisan bid from {$bid->artisan->first_name} for " . format_money($bid->amount, $task->complaint->apartment->currency) . " has been accepted and funds secured in escrow.");

                        try {
                            Mail::to($bid->artisan->email)->send(new ArtisanAssignedMail($task, $code));
                        } catch (\Exception $e) {}

                        return redirect()->route('artisan.tasks.show', $task->id)->with('success', 'Payment successful. The artisan has been notified via email and funds are in escrow.');
                    }
                }
            }

            return redirect()->route('dashboard')->with('error', 'Payment failed or was cancelled.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Paystack Callback Error: " . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Payment verification failed. If you were debited, please contact support.');
        }
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

        $request->validate([
            'rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        $task->update(['status' => 'completed']);
        $acceptedBid = $task->bids()->where('status', 'accepted')->first();

        // Save Rating if provided
        if ($acceptedBid && $request->rating) {
            try {
                \App\Models\ArtisanRating::create([
                    'artisan_id' => $acceptedBid->artisan_id,
                    'user_id' => $user->user_id,
                    'task_id' => $task->id,
                    'rating' => $request->rating,
                    'comment' => $request->comment,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to save artisan rating on task completion: " . $e->getMessage());
            }
        }

        // Handle Escrow Payout Logic
        if ($task->payment_status === 'escrowed' && $acceptedBid) {
            $task->update([
                'payment_status' => 'released'
            ]);

            // Notify Admin for Manual Payout Disbursement
            // This assumes admin manually transfers the funds to the artisan's bank account
            // using the platform's Paystack Dashboard, since API transfers aren't fully implemented
            $adminEmail = env('ADMIN_EMAIL', 'admin@easyrent.com');
            try {
                Mail::raw("Task #{$task->id} has been completed. Please disburse " . format_money($acceptedBid->amount) . " to Artisan {$acceptedBid->artisan->first_name} (Bank: {$acceptedBid->artisan->bank_name}, Acct: {$acceptedBid->artisan->bank_account_number}).", function ($message) use ($adminEmail) {
                    $message->to($adminEmail)->subject('Artisan Payout Required');
                });
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send admin payout notification: " . $e->getMessage());
            }

            $task->complaint->addComment($user, "Task completed. Funds are being released to the artisan's bank account.");
        } 
        // Rent Set-off Logic (Legacy fallback)
        elseif ($task->request_setoff && $task->tenant_id && $acceptedBid) {
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

            $task->complaint->addComment($user, "Rent set-off of " . format_money($acceptedBid->amount, $task->complaint->apartment->currency) . " has been recorded.");
        } else {
            $task->complaint->addComment($user, "Artisan task marked as completed.");
        }

        return back()->with('success', 'Task marked as completed successfully.');
    }

    /**
     * Cancel an assigned task
     */
    public function cancelTask(Request $request, ArtisanTask $task)
    {
        $user = Auth::user();
        if ($user->user_id != $task->landlord_id && $user->user_id != $task->tenant_id && !$user->admin) {
            return back()->with('error', 'Unauthorized action.');
        }

        if ($task->status !== 'assigned') {
            return back()->with('error', 'Only assigned tasks can be cancelled.');
        }

        $request->validate([
            'reason' => 'required|string|min:5',
            'rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        $acceptedBid = $task->bids()->where('status', 'accepted')->first();

        // 1. Mark accepted bid as cancelled
        if ($acceptedBid) {
            $acceptedBid->update(['status' => 'cancelled']);
        }

        // 2. Rate the artisan if rating is provided
        if ($acceptedBid && $request->rating) {
            \App\Models\ArtisanRating::create([
                'artisan_id' => $acceptedBid->artisan_id,
                'user_id' => $user->user_id,
                'task_id' => $task->id,
                'rating' => $request->rating,
                'comment' => $request->comment ?? $request->reason,
            ]);
        }

        // 3. Log the cancellation and reason as a comment/update on complaint
        $task->complaint->addComment($user, "Artisan task assignment cancelled. Reason/Complaint: " . $request->reason);

        // 4. Reset task status back to 'open' so other artisans can bid / be assigned
        $task->update(['status' => 'open']);

        // 5. Delete verification code
        if ($task->verificationCode) {
            $task->verificationCode->delete();
        }

        return back()->with('success', 'Artisan assignment has been cancelled and complaint registered.');
    }
}