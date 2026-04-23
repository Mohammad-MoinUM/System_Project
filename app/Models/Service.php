<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'provider_id',
        'name',
        'description',
        'category',
        'price',
        'is_active',
        'is_insured',
        'guarantee_enabled',
        'flash_deal_price',
        'flash_deal_ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_insured' => 'boolean',
        'guarantee_enabled' => 'boolean',
        'flash_deal_ends_at' => 'datetime',
        'price' => 'decimal:2',
        'flash_deal_price' => 'decimal:2',
    ];

    public function getEffectivePriceAttribute(): float
    {
        if (!empty($this->flash_deal_price) && $this->flash_deal_ends_at && now()->lt($this->flash_deal_ends_at)) {
            return (float) $this->flash_deal_price;
        }

        return (float) ($this->price ?? 0);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
