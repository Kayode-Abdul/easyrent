<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplaintCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon',
        'priority_level',
        'estimated_resolution_hours',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'estimated_resolution_hours' => 'integer'
    ];

    /**
     * Get complaints for this category
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'category_id');
    }

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for categories by priority level
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority_level', $priority);
    }

    /**
     * Get formatted priority level
     */
    public function getPriorityLevelFormattedAttribute(): string
    {
        return ucfirst($this->priority_level);
    }

    /**
     * Get priority color for UI
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority_level) {
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'urgent' => 'dark',
            default => 'secondary'
        };
    }

    /**
     * Get estimated resolution time formatted
     */
    public function getEstimatedResolutionFormattedAttribute(): string
    {
        $hours = $this->estimated_resolution_hours;
        
        if ($hours < 24) {
            return $hours . ' hour' . ($hours !== 1 ? 's' : '');
        }
        
        $days = round($hours / 24, 1);
        return $days . ' day' . ($days !== 1 ? 's' : '');
    }
}