<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\ComplaintAttachment;
use App\Models\Apartment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\ComplaintNotification;
use Illuminate\Support\Str;

class ComplaintController extends Controller
{
    /**
     * Display complaints for the authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Complaint::with(['category', 'tenant', 'landlord', 'apartment.property', 'assignedTo']);

        // Filter based on user role
        if ($user->isTenant()) {
            $query->forTenant($user->user_id);
        } elseif ($user->isLandlord()) {
            $query->forLandlord($user->user_id);
        } elseif ($user->isAgent()) {
            $query->assignedTo($user->user_id);
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $complaints = $query->orderBy('created_at', 'desc')->paginate(15);
        $categories = ComplaintCategory::active()->get();

        return view('complaints.index', compact('complaints', 'categories'));
    }

    /**
     * Show the form for creating a new complaint
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isTenant()) {
            abort(403, 'Only tenants can create complaints.');
        }

        // Get tenant's apartments
        $apartments = Apartment::where('tenant_id', $user->user_id)
            ->with(['property', 'owner'])
            ->get();

        if ($apartments->isEmpty()) {
            return redirect()->route('dashboard')
                ->with('error', 'You must be assigned to an apartment to submit a complaint.');
        }

        $categories = ComplaintCategory::active()->orderBy('name')->get();
        $selectedApartment = $request->apartment_id;

        return view('complaints.create', compact('apartments', 'categories', 'selectedApartment'));
    }

    /**
     * Store a newly created complaint
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isTenant()) {
            abort(403, 'Only tenants can create complaints.');
        }

        $request->validate([
            'apartment_id' => 'required|exists:apartments,apartment_id',
            'category_id' => 'required|exists:complaint_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'priority' => 'required|in:low,medium,high,urgent',
            'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx'
        ]);

        // Verify tenant has access to this apartment
        $apartment = Apartment::where('apartment_id', $request->apartment_id)
            ->where('tenant_id', $user->user_id)
            ->with(['property', 'owner'])
            ->firstOrFail();

        // Create complaint
        $complaint = Complaint::create([
            'tenant_id' => $user->user_id,
            'landlord_id' => $apartment->user_id,
            'apartment_id' => $apartment->apartment_id,
            'property_id' => $apartment->property_id,
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
        ]);

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $this->storeAttachment($complaint, $file, $user);
            }
        }

        // Auto-assign to property agent if available
        if ($apartment->property->agent_id) {
            $complaint->assignTo(
                User::where('user_id', $apartment->property->agent_id)->first(),
                $user
            );
        }

        // Send notification to landlord
        try {
            $landlord = User::where('user_id', $apartment->user_id)->first();
            if ($landlord && $landlord->email) {
                Mail::to($landlord->email)->send(new ComplaintNotification($complaint, 'new'));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send complaint notification: ' . $e->getMessage());
        }

        return redirect()->route('complaints.show', $complaint)
            ->with('success', 'Complaint submitted successfully. Complaint number: ' . $complaint->complaint_number);
    }

    /**
     * Display the specified complaint
     */
    public function show(Complaint $complaint)
    {
        $user = Auth::user();
        
        // Check access permissions
        if (!$this->canViewComplaint($complaint, $user)) {
            abort(403, 'You do not have permission to view this complaint.');
        }

        $complaint->load([
            'category',
            'tenant',
            'landlord',
            'apartment.property',
            'assignedTo',
            'resolvedBy',
            'updates.user',
            'attachments.uploadedBy'
        ]);

        // Get public updates for tenant/landlord, all updates for agents/admins
        $updates = $complaint->updates();
        if (!($user->isAgent() || $user->admin)) {
            $updates = $updates->public();
        }
        $updates = $updates->orderBy('created_at', 'asc')->get();

        return view('complaints.show', compact('complaint', 'updates'));
    }

    /**
     * Add comment to complaint
     */
    public function addComment(Request $request, Complaint $complaint)
    {
        $user = Auth::user();
        
        if (!$this->canViewComplaint($complaint, $user)) {
            abort(403, 'You do not have permission to comment on this complaint.');
        }

        $request->validate([
            'message' => 'required|string|min:5',
            'is_internal' => 'boolean'
        ]);

        $isInternal = $request->boolean('is_internal') && ($user->isAgent() || $user->admin);

        $complaint->addComment($user, $request->message, $isInternal);

        return redirect()->route('complaints.show', $complaint)
            ->with('success', 'Comment added successfully.');
    }

    /**
     * Update complaint status
     */
    public function updateStatus(Request $request, Complaint $complaint)
    {
        $user = Auth::user();
        
        // Only landlords, agents, and admins can update status
        if (!($user->isLandlord() || $user->isAgent() || $user->admin)) {
            abort(403, 'You do not have permission to update complaint status.');
        }

        // Landlords can only update their own property complaints
        if ($user->isLandlord() && $complaint->landlord_id !== $user->user_id) {
            abort(403, 'You can only update complaints for your own properties.');
        }

        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed,escalated',
            'notes' => 'nullable|string'
        ]);

        $complaint->updateStatus($request->status, $user, $request->notes);

        // Send notification to tenant
        try {
            $tenant = $complaint->tenant;
            if ($tenant && $tenant->email) {
                Mail::to($tenant->email)->send(new ComplaintNotification($complaint, 'status_update'));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send status update notification: ' . $e->getMessage());
        }

        return redirect()->route('complaints.show', $complaint)
            ->with('success', 'Complaint status updated successfully.');
    }

    /**
     * Assign complaint to user
     */
    public function assign(Request $request, Complaint $complaint)
    {
        $user = Auth::user();
        
        // Only landlords, agents, and admins can assign complaints
        if (!($user->isLandlord() || $user->isAgent() || $user->admin)) {
            abort(403, 'You do not have permission to assign complaints.');
        }

        $request->validate([
            'assigned_to' => 'required|exists:users,user_id'
        ]);

        $assignee = User::where('user_id', $request->assigned_to)->firstOrFail();
        $complaint->assignTo($assignee, $user);

        return redirect()->route('complaints.show', $complaint)
            ->with('success', 'Complaint assigned successfully.');
    }

    /**
     * Landlord dashboard for complaints
     */
    public function landlordDashboard()
    {
        $user = Auth::user();
        
        if (!$user->isLandlord()) {
            abort(403, 'Access denied.');
        }

        $complaints = Complaint::forLandlord($user->user_id)
            ->with(['category', 'tenant', 'apartment.property'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'total' => Complaint::forLandlord($user->user_id)->count(),
            'open' => Complaint::forLandlord($user->user_id)->open()->count(),
            'resolved' => Complaint::forLandlord($user->user_id)->resolved()->count(),
            'overdue' => Complaint::forLandlord($user->user_id)->overdue()->count(),
        ];

        return view('complaints.landlord-dashboard', compact('complaints', 'stats'));
    }

    /**
     * Store file attachment
     */
    private function storeAttachment(Complaint $complaint, $file, User $user): ComplaintAttachment
    {
        $originalName = $file->getClientOriginalName();
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('complaints/' . $complaint->id, $fileName, 'public');
        $fileHash = hash_file('sha256', $file->getRealPath());

        return ComplaintAttachment::create([
            'complaint_id' => $complaint->id,
            'uploaded_by' => $user->user_id,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'original_name' => $originalName,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'file_hash' => $fileHash
        ]);
    }

    /**
     * Check if user can view complaint
     */
    private function canViewComplaint(Complaint $complaint, User $user): bool
    {
        // Admin can view all
        if ($user->admin) {
            return true;
        }

        // Tenant can view their own complaints
        if ($user->isTenant() && $complaint->tenant_id === $user->user_id) {
            return true;
        }

        // Landlord can view complaints for their properties
        if ($user->isLandlord() && $complaint->landlord_id === $user->user_id) {
            return true;
        }

        // Agent can view assigned complaints
        if ($user->isAgent() && $complaint->assigned_to === $user->user_id) {
            return true;
        }

        return false;
    }
}