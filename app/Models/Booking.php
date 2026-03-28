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

    /**
     * Get the company this booking belongs to (if corporate)
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch this booking is for (if corporate)
     */
    public function branch()
    {
        return $this->belongsTo(CompanyBranch::class, 'branch_id');
    }

    /**
     * Get who requested this booking (if corporate)
     */
    public function requestedByUser()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get who approved this booking (if corporate)
     */
    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Filter bookings by status
     */
    public function scopeFilterByStatus($query, $status = null)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Filter bookings by branch
     */
    public function scopeFilterByBranch($query, $branchId = null)
    {
        if ($branchId) {
            return $query->where('branch_id', $branchId);
        }
        return $query;
    }
}
