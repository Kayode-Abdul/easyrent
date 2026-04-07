<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyAttribute extends Model
{
    protected $fillable = [
        'property_id',
        'attribute_key',
        'attribute_value',
    ];

    /**
     * Get the property that owns this attribute
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }

    /**
     * Get the value as JSON if it's a JSON string
     */
    public function getValueAsJson()
    {
        $decoded = json_decode($this->attribute_value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $this->attribute_value;
    }

    /**
     * Set value from array (will be stored as JSON)
     */
    public function setValueFromArray(array $value): void
    {
        $this->attribute_value = json_encode($value);
    }
}
