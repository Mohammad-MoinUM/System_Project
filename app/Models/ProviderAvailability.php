<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderAvailability extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
    ];

    /**
     * Get the provider (user) that owns this availability.
     */
    public function provider()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope to get availability for specific day
     */
    public function scopeForDay($query, $day)
    {
        return $query->where('day_of_week', $day)->where('is_available', true);
    }

    /**
     * Scope to get availability for specific provider
     */
    public function scopeForProvider($query, $providerId)
    {
        return $query->where('user_id', $providerId);
    }
}
