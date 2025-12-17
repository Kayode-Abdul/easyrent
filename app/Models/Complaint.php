<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Complaint extends Model
{
    protected $fillable = [
        'complaint_number',
        'tenant_id',
        'landlord_id',
        'apartment_id',
        'property_id',
        'category_id',
        'title',
        'description',
        'priority',
        'status',
        'assigned_to',
        'resolution_notes',
        'resolved_at',
        'resolved_by',
        'metadata'
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'metadata' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($complaint) {
            if (empty($complaint->complaint_number)) {
                $complaint->complaint_number = self::generateComplaintNumber();
            }
        });
    }

    /**
     * Generate unique complaint number
     */
    public static function generateComplaintNumber(): string
    {
        $year = date('Y');
        $prefix = "CMP-{$year}-";
        
        // Get the last complaint number for this year
        $lastComplaint = self::where('complaint_number', 'like', $prefix . '%')
            ->orderBy('complaint_number', 'desc')
            ->first();
        
        if ($lastComplaint) {
            $lastNumber = (int) str_replace($prefix, '', $lastComplaint->complaint_number);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id', 'user_id');
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id', 'user_id');
    }

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class, 'apartment_id', 'apartment_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ComplaintCategory::class, 'category_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by', 'user_id');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(ComplaintUpdate::class)->orderBy('created_at', 'desc');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ComplaintAttachment::class);
    }

    /**
     * Scopes
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForLandlord($query, $landlordId)
    {
        return $query->where('landlord_id', $landlordId);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    public function scopeResolved($query)
    {
        return $query->whereIn('status', ['resolved', 'closed']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('created_at', '<', now()->subHours(48))
                    ->whereIn('status', ['open', 'in_progress']);
    }

    /**
     * Status and priority helpers
     */
    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'in_progress']);
    }

    public function isResolved(): bool
    {
        return in_array($this->status, ['resolved', 'closed']);
    }

    public function isOverdue(): bool
    {
        if ($this->isResolved()) {
            return false;
        }
        
        $expectedResolutionHours = $this->category->estimated_resolution_hours ?? 24;
        return $this->created_at->addHours($expectedResolutionHours)->isPast();
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open' => 'danger',
            'in_progress' => 'warning',
            'resolved' => 'success',
            'closed' => 'secondary',
            'escalated' => 'dark',
            default => 'secondary'
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'urgent' => 'dark',
            default => 'secondary'
        };
    }

    public function getStatusFormattedAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getPriorityFormattedAttribute(): string
    {
        return ucfirst($this->priority);
    }

    /**
     * Time tracking
     */
    public function getAgeInHoursAttribute(): int
    {
        return $this->created_at->diffInHours(now());
    }

    public function getResolutionTimeAttribute(): ?int
    {
        if (!$this->resolved_at) {
            return null;
        }
        
        return $this->created_at->diffInHours($this->resolved_at);
    }

    /**
     * Actions
     */
    public function assignTo(User $user, User $assignedBy): void
    {
        $oldAssignee = $this->assigned_to;
        
        $this->update(['assigned_to' => $user->user_id]);
        
        $this->updates()->create([
            'user_id' => $assignedBy->user_id,
            'update_type' => 'assignment',
            'message' => "Complaint assigned to {$user->first_name} {$user->last_name}",
            'old_value' => $oldAssignee ? User::find($oldAssignee)->first_name ?? 'Unassigned' : 'Unassigned',
            'new_value' => "{$user->first_name} {$user->last_name}"
        ]);
    }

    public function updateStatus(string $newStatus, User $updatedBy, string $notes = null): void
    {
        $oldStatus = $this->status;
        
        $updateData = ['status' => $newStatus];
        
        if ($newStatus === 'resolved' && !$this->resolved_at) {
            $updateData['resolved_at'] = now();
            $updateData['resolved_by'] = $updatedBy->user_id;
            
            if ($notes) {
                $updateData['resolution_notes'] = $notes;
            }
        }
        
        $this->update($updateData);
        
        $this->updates()->create([
            'user_id' => $updatedBy->user_id,
            'update_type' => 'status_change',
            'message' => $notes ?? "Status changed from {$oldStatus} to {$newStatus}",
            'old_value' => $oldStatus,
            'new_value' => $newStatus
        ]);
    }

    public function addComment(User $user, string $message, bool $isInternal = false): ComplaintUpdate
    {
        return $this->updates()->create([
            'user_id' => $user->user_id,
            'update_type' => 'comment',
            'message' => $message,
            'is_internal' => $isInternal
        ]);
    }

    public function escalate(User $escalatedBy, string $reason): void
    {
        $this->update(['status' => 'escalated']);
        
        $this->updates()->create([
            'user_id' => $escalatedBy->user_id,
            'update_type' => 'escalation',
            'message' => "Complaint escalated: {$reason}",
            'old_value' => $this->status,
            'new_value' => 'escalated'
        ]);
    }
}