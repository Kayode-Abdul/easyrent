<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PropertyImage extends Model
{
    protected $fillable = [
        'property_id',
        'apartment_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'original_name',
        'file_size',
        'mime_type',
        'is_main',
        'order'
    ];

    /**
     * Relationships
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class , 'property_id', 'property_id');
    }

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class , 'apartment_id', 'apartment_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class , 'uploaded_by', 'user_id');
    }

    /**
     * Accessors
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Boot method to handle automatic file deletion
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($image) {
            if (Storage::exists($image->file_path)) {
                Storage::delete($image->file_path);
            }
        });
    }

    /**
     * Scope to get the main image
     */
    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }
}