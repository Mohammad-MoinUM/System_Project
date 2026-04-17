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
        'booking_mode',
        'recurrence_type',
        'recurrence_interval',
        'recurrence_end_date',
        'extra_service_ids',
        'scheduled_at',
        'booking_date',
        'time_from',
        'time_to',
        'slot_duration_minutes',
        'total',
        'notes',
        'service_address_label',
        'service_address_line1',
        'service_address_line2',
        'service_city',
        'service_area',
        'service_postal_code',
        'attachments',
        'tracking_status',
        'provider_latitude',
        'provider_longitude',
        'tracking_updated_at',
        'estimated_arrival_at',
        'payment_method',
        'payment_split_type',
        'upfront_amount',
        'remaining_amount',
        'payment_status',
        'cashback_amount',
        'paid_at',
        'cashback_credited_at',
        'receipt_number',
        'cancellation_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'booking_mode' => 'string',
        'recurrence_interval' => 'integer',
        'recurrence_end_date' => 'date',
        'extra_service_ids' => 'array',
        'scheduled_at' => 'datetime',
        'booking_date' => 'date',
        'attachments' => 'array',
        'cancelled_at' => 'datetime',
        'provider_latitude' => 'decimal:7',
        'provider_longitude' => 'decimal:7',
        'tracking_updated_at' => 'datetime',
        'estimated_arrival_at' => 'datetime',
        'upfront_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'cashback_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'cashback_credited_at' => 'datetime',
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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function refundRequests(): HasMany
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
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
