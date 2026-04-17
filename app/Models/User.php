<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
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
        'referral_code',
        'referred_by_user_id',
        'preferred_time_slots',
        'provider_gender_preference',
        'loyalty_points',
        'referral_reward_claimed_at',
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
            'preferred_time_slots' => 'array',
            'referral_reward_claimed_at' => 'datetime',
            'loyalty_points' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (empty($user->referral_code)) {
                do {
                    $user->referral_code = Str::upper(Str::random(8));
                } while (self::where('referral_code', $user->referral_code)->exists());
            }
        });
    }

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by_user_id');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by_user_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
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

    public function supportConversations(): HasMany
    {
        return $this->hasMany(SupportConversation::class, 'user_id');
    }

    public function supportMessages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'sender_id');
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function refundRequests(): HasMany
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function serviceAreas(): HasMany
    {
        return $this->hasMany(ProviderServiceArea::class, 'user_id');
    }

    public function payoutRequests(): HasMany
    {
        return $this->hasMany(ProviderPayoutRequest::class, 'user_id');
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

        return $membership && in_array($membership->role, ['admin', 'manager', 'approver'], true);
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

        return $membership && in_array($membership->role, ['admin', 'manager', 'requester', 'finance'], true);
    }
}
