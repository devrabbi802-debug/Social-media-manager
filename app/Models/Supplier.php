<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'company',
        'tax_number',
        'address',
        'payment_terms',
        'payment_term_days',
        'opening_balance',
        'notes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'payment_term_days' => 'integer',
        'opening_balance' => 'decimal:2',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(PurchaseReceipt::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Total purchased value from non-cancelled invoices.
     */
    public function totalPurchases(): float
    {
        return round((float) $this->invoices()
            ->where('status', '!=', 'cancelled')
            ->sum('total'), 2);
    }

    /**
     * Total money paid to this supplier (completed payments only).
     */
    public function totalPaid(): float
    {
        return round((float) $this->payments()
            ->where('status', 'completed')
            ->sum('amount'), 2);
    }

    public function totalReturns(): float
    {
        return round((float) $this->returns()
            ->where('status', 'completed')
            ->sum('total'), 2);
    }

    /**
     * Outstanding balance owed to the supplier.
     */
    public function balance(): float
    {
        return round((float) $this->opening_balance + $this->totalPurchases() - $this->totalPaid() - $this->totalReturns(), 2);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, ?string $search)
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('company', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }
}
