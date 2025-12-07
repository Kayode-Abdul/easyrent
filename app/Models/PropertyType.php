<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyType extends Model
{
    protected $fillable = [
        'name',
        'category',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all properties of this type
     */
    public function properties()
    {
        return $this->hasMany(Property::class, 'prop_type', 'id');
    }

    /**
     * Scope to get only active types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get residential property types
     */
    public static function residential()
    {
        return static::where('category', 'residential')->active()->get();
    }

    /**
     * Get commercial property types
     */
    public static function commercial()
    {
        return static::where('category', 'commercial')->active()->get();
    }

    /**
     * Get land/agricultural property types
     */
    public static function land()
    {
        return static::where('category', 'land')->active()->get();
    }
}
