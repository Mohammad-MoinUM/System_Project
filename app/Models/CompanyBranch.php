<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyBranch extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id',
        'branch_name',
        'address',
        'city',
        'postal_code',
        'phone',
        'branch_manager_name',
        'branch_manager_id',
        'is_active',
    ];

    /**
     * Get the company this branch belongs to
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch manager
     */
    public function branchManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'branch_manager_id');
    }

    /**
     * Get all staff members assigned to this branch
     */
    public function staff(): HasMany
    {
        return $this->hasMany(CompanyUserMembership::class, 'branch_id');
    }

    /**
     * Get all service requests for this branch
     */
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(CompanyServiceRequest::class);
    }

    /**
     * Get all bookings for this branch
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Scope to active branches only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
