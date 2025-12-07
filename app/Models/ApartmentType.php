<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApartmentType extends Model
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
     * Get all apartments of this type
     */
    public function apartments()
    {
        return $this->hasMany(Apartment::class, 'apartment_type_id', 'id');
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
     * Get residential apartment types
     */
    public static function residential()
    {
        return static::where('category', 'residential')->active()->get();
    }

    /**
     * Get commercial apartment types
     */
    public static function commercial()
    {
        return static::where('category', 'commercial')->active()->get();
    }

    /**
     * Get other apartment types
     */
    public static function other()
    {
        return static::where('category', 'other')->active()->get();
    }

    /**
     * Get apartment type ID by name (for migration purposes)
     */
    public static function getIdByName($name)
    {
        $type = static::where('name', $name)->first();
        return $type ? $type->id : null;
    }
}
