<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseInvoice extends Model
{
    public const STATUSES = [
        'draft' => 'Draft',
        'awaiting_payment' => 'Awaiting Payment',
        'partially_paid' => 'Partially Paid',
        'paid' => 'Paid',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'invoice_number',
        'purchase_order_id',
        'purchase_receipt_id',
        'supplier_id',
        'invoice_date',
        'due_date',
        'status',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'total',
        'paid_amount',
        'advance_applied',
        'notes',
        'created_by',
        'posted_at',
        'cancelled_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_type' => 'string',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'advance_applied' => 'decimal:2',
        'posted_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (PurchaseInvoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $prefix = PurchaseSetting::current()->inv_prefix ?: 'INV';
                $invoice->invoice_number = $prefix.'-'.date('Ymd-His').'-'.random_int(10, 99);
            }
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function purchaseReceipt(): BelongsTo
    {
        return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
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

    public function due(): float
    {
        return round((float) $this->total - (float) $this->paid_amount - (float) $this->advance_applied, 2);
    }

    public function isPaid(): bool
    {
        return $this->due() < 0.01;
    }

    public function isOverdue(): bool
    {
        return $this->due() > 0.01 && $this->due_date && $this->due_date->isPast();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (! $search) {
            return $query;
        }

        return $query->where('invoice_number', 'like', "%{$search}%");
    }
}
