<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyInvoice extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax',
        'total',
        'status',
        'paid_at',
        'month',
        'year',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    /**
     * Get the company this invoice belongs to
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope to paid invoices
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope to unpaid invoices
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['draft', 'sent', 'viewed', 'overdue']);
    }

    /**
     * Scope to invoices for specific month/year
     */
    public function scopeForMonth($query, $month, $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }
}
