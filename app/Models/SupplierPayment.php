<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierPayment extends Model
{
    protected $fillable = [
        'payment_number',
        'supplier_id',
        'purchase_invoice_id',
        'purchase_order_id',
        'amount',
        'method',
        'reference',
        'payment_date',
        'notes',
        'status',
        'type',
        'created_by',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'paid_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (SupplierPayment $payment) {
            if (empty($payment->payment_number)) {
                $prefix = PurchaseSetting::current()->pay_prefix ?: 'PAY';
                $payment->payment_number = $prefix.'-'.date('Ymd-His').'-'.random_int(10, 99);
            }
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function methods(): HasMany
    {
        return $this->hasMany(SupplierPaymentMethod::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function methodName(): string
    {
        $account = ChartOfAccount::byCode($this->method);

        return $account?->name ?? ucfirst(str_replace('_', ' ', $this->method));
    }
}
