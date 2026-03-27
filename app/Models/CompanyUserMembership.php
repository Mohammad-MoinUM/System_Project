<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyUserMembership extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id',
        'user_id',
        'branch_id',
        'role',
        'is_active',
        'invited_at',
        'joined_at',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'invited_at' => 'datetime',
            'joined_at' => 'datetime',
        ];
    }

    /**
     * Get the company
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the branch (if assigned)
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(CompanyBranch::class, 'branch_id');
    }

    /**
     * Scope to active members only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if member has admin role
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if member has manager role
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Check if member can approve requests
     */
    public function canApprove(): bool
    {
        return in_array($this->role, ['admin', 'manager', 'approver']);
    }

    /**
     * Check if member can request services
     */
    public function canRequest(): bool
    {
        return in_array($this->role, ['admin', 'manager', 'requester']);
    }
}
