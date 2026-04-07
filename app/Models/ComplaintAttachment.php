<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ComplaintAttachment extends Model
{
    protected $fillable = [
        'complaint_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'original_name',
        'file_size',
        'mime_type',
        'file_hash'
    ];

    /**
     * Relationships
     */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'user_id');
    }

    /**
     * Helpers
     */
    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getFileIconAttribute(): string
    {
        if ($this->isImage()) {
            return 'nc-icon nc-image';
        }
        
        return match(true) {
            str_contains($this->mime_type, 'pdf') => 'nc-icon nc-paper',
            str_contains($this->mime_type, 'word') => 'nc-icon nc-paper',
            str_contains($this->mime_type, 'excel') => 'nc-icon nc-chart-bar-32',
            str_contains($this->mime_type, 'video') => 'nc-icon nc-button-play',
            default => 'nc-icon nc-attach-87'
        };
    }

    /**
     * Delete file when model is deleted
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($attachment) {
            if (Storage::exists($attachment->file_path)) {
                Storage::delete($attachment->file_path);
            }
        });
    }
}