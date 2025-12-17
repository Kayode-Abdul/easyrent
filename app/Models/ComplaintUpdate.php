<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintUpdate extends Model
{
    protected $fillable = [
        'complaint_id',
        'user_id',
        'update_type',
        'message',
        'old_value',
        'new_value',
        'is_internal',
        'metadata'
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'metadata' => 'array'
    ];

    /**
     * Relationships
     */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Scopes
     */
    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('update_type', $type);
    }

    /**
     * Helpers
     */
    public function getUpdateTypeFormattedAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->update_type));
    }

    public function getUpdateIconAttribute(): string
    {
        return match($this->update_type) {
            'comment' => 'nc-icon nc-chat-33',
            'status_change' => 'nc-icon nc-refresh-69',
            'assignment' => 'nc-icon nc-single-02',
            'escalation' => 'nc-icon nc-alert-circle-i',
            'priority_change' => 'nc-icon nc-priority',
            default => 'nc-icon nc-bullet-list-67'
        };
    }

    public function getUpdateColorAttribute(): string
    {
        return match($this->update_type) {
            'comment' => 'primary',
            'status_change' => 'info',
            'assignment' => 'success',
            'escalation' => 'danger',
            'priority_change' => 'warning',
            default => 'secondary'
        };
    }
}