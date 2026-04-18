<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegionalScope extends Model
{
    protected $fillable = [
        'user_id',
        'scope_type',
        'scope_value',
        'country_name',
        'state_id',
        'lga_id'
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get scopes for a user organized by type
     */
    public static function getScopesForUser($userId)
    {
        return static::where('user_id', $userId)
            ->get()
            ->groupBy('scope_type');
    }

    /**
     * Create state and LGA scopes for a user
     */
    public static function createScopes($userId, $states, $lgas = [], $countries = [])
    {
        foreach ((array)$states as $idx => $state) {
            if (!$state) continue;
            
            $country = $countries[$idx] ?? null;
            
            // Create state scope
            static::firstOrCreate([
                'user_id' => $userId,
                'scope_type' => 'state',
                'scope_value' => $state,
                'country_name' => $country
            ]);
            
            // Create LGA scope if provided
            $lga = $lgas[$idx] ?? null;
            if ($lga) {
                static::firstOrCreate([
                    'user_id' => $userId,
                    'scope_type' => 'lga',
                    'scope_value' => $state . '::' . $lga, // Store as state::lga format
                    'country_name' => $country
                ]);
            }
        }
    }

    /**
     * Get formatted scopes for display
     */
    public function getFormattedScopeAttribute()
    {
        if ($this->scope_type === 'lga' && strpos($this->scope_value, '::') !== false) {
            [$state, $lga] = explode('::', $this->scope_value, 2);
            return "{$lga}, {$state}";
        }
        
        return $this->scope_value;
    }
}
