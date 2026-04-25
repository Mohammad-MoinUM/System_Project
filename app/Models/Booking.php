<?php

namespace App\Models;

use Carbon\CarbonInterface;
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
        'promo_code',
        'discount_amount',
        'original_total',
        'upfront_amount',
        'remaining_amount',
        'payment_status',
        'escrow_status',
        'provider_completed_at',
        'customer_confirmed_at',
        'escrow_released_at',
        'cashback_amount',
        'paid_at',
        'cashback_credited_at',
        'receipt_number',
        'cancellation_reason',
        'emergency_cancel_flag',
        'cancellation_fee',
        'cancellation_policy_note',
        'sos_triggered',
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
        'discount_amount' => 'decimal:2',
        'original_total' => 'decimal:2',
        'cancellation_fee' => 'decimal:2',
        'cashback_amount' => 'decimal:2',
        'provider_completed_at' => 'datetime',
        'customer_confirmed_at' => 'datetime',
        'escrow_released_at' => 'datetime',
        'paid_at' => 'datetime',
        'cashback_credited_at' => 'datetime',
        'total' => 'decimal:2',
        'emergency_cancel_flag' => 'boolean',
        'sos_triggered' => 'boolean',
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

    public function tipTotal(): float
    {
        if ($this->relationLoaded('payments')) {
            return (float) $this->payments->where('type', 'tip')->sum('amount');
        }

        return (float) $this->payments()->where('type', 'tip')->sum('amount');
    }

    public function totalWithTips(): float
    {
        return (float) $this->total + $this->tipTotal();
    }

    public static function completedTotalWithTipsForUser(string $userColumn, int $userId, ?CarbonInterface $start = null, ?CarbonInterface $end = null): float
    {
        $bookingQuery = static::query()
            ->where($userColumn, $userId)
            ->where('status', 'completed');

        if ($start !== null && $end !== null) {
            $bookingQuery->whereBetween('updated_at', [$start, $end]);
        } elseif ($start !== null) {
            $bookingQuery->where('updated_at', '>=', $start);
        } elseif ($end !== null) {
            $bookingQuery->where('updated_at', '<=', $end);
        }

        $bookingIds = (clone $bookingQuery)->select('id');

        $bookingTotal = (float) (clone $bookingQuery)->sum('total');
        $tipTotal = (float) Payment::query()
            ->where('type', 'tip')
            ->whereIn('booking_id', $bookingIds)
            ->sum('amount');

        return $bookingTotal + $tipTotal;
    }

    public function refundRequests(): HasMany
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(BookingChatMessage::class);
    }

    public function safetyAlerts(): HasMany
    {
        return $this->hasMany(SafetyAlert::class);
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
