<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'service_id',
        'taker_id',
        'provider_id',
        'status',
        'scheduled_at',
        'booking_date',
        'time_from',
        'time_to',
        'slot_duration_minutes',
        'total',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'booking_date' => 'date',
        'total' => 'decimal:2',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function taker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'taker_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
