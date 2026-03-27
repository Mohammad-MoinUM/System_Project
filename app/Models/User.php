<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'role',
        'verification_status',
        'rejection_reason',
        'verified_at',
        'verified_by',
        'phone',
        'alt_phone',
        'city',
        'area',
        'photo',
        'onboarding_completed',
        'education',
        'institution',
        'expertise',
        'bio',
        'experience_years',
        'services_offered',
        'certifications',
        'nid_number',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'verified_at' => 'datetime',
            'password' => 'hashed',
            'onboarding_completed' => 'boolean',
            'services_offered' => 'array',
            'certifications' => 'array',
        ];
    }

    public function servicesProvided(): HasMany
    {
        return $this->hasMany(Service::class, 'provider_id');
    }

    public function bookingsAsProvider(): HasMany
    {
        return $this->hasMany(Booking::class, 'provider_id');
    }

    public function bookingsAsTaker(): HasMany
    {
        return $this->hasMany(Booking::class, 'taker_id');
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'provider_id');
    }

    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(Review::class, 'taker_id');
    }

    public function savedProviders(): HasMany
    {
        return $this->hasMany(SavedProvider::class, 'taker_id');
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(ProviderAvailability::class, 'user_id');
    }

    /**
     * Get companies where this user is the primary admin
     */
    public function companiesAsAdmin(): HasMany
    {
        return $this->hasMany(Company::class, 'primary_admin_id');
    }

    /**
     * Get all company memberships for this user
     */
    public function companyMemberships(): HasMany
    {
        return $this->hasMany(CompanyUserMembership::class);
    }

    /**
     * Get service requests made by this user
     */
    public function serviceRequestsMade(): HasMany
    {
        return $this->hasMany(CompanyServiceRequest::class, 'requested_by');
    }

    /**
     * Get service requests approved by this user
     */
    public function serviceRequestsApproved(): HasMany
    {
        return $this->hasMany(CompanyServiceRequest::class, 'approved_by');
    }

    /**
     * Check if user is part of a company
     */
    public function isPartOfCompany($companyId): bool
    {
        return $this->companyMemberships()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get user's role in a specific company
     */
    public function getRoleInCompany($companyId): ?string
    {
        return $this->companyMemberships()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->value('role');
    }

    /**
     * Check if user can approve in a company
     */
    public function canApproveInCompany($companyId): bool
    {
        $membership = $this->companyMemberships()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->first();

        return $membership && $membership->canApprove();
    }

    /**
     * Check if user can request services in a company
     */
    public function canRequestInCompany($companyId): bool
    {
        $membership = $this->companyMemberships()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->first();

        return $membership && $membership->canRequest();
    }
}
