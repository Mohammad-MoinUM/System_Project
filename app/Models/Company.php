<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_name',
        'email',
        'phone',
        'address',
        'city',
        'postal_code',
        'contact_person_name',
        'company_registration_number',
        'company_documents_path',
        'status',
        'primary_admin_id',
        'rejection_reason',
        'approved_at',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Get the primary admin of this company
     */
    public function primaryAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_admin_id');
    }

    /**
     * Get all branches of this company
     */
    public function branches(): HasMany
    {
        return $this->hasMany(CompanyBranch::class);
    }

    /**
     * Get all staff members of this company
     */
    public function staff(): HasMany
    {
        return $this->hasMany(CompanyUserMembership::class);
    }

    /**
     * Get all service requests for this company
     */
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(CompanyServiceRequest::class);
    }

    /**
     * Get all bookings for this company
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get all invoices for this company
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(CompanyInvoice::class);
    }

    /**
     * Scope to approved companies only
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to pending companies
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
