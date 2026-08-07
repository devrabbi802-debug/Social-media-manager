<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReceipt extends Model
{
    protected $fillable = [
        'receipt_number',
        'purchase_order_id',
        'supplier_id',
        'warehouse_id',
        'receipt_date',
        'status',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'total',
        'notes',
        'created_by',
        'received_at',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_type' => 'string',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'received_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (PurchaseReceipt $receipt) {
            if (empty($receipt->receipt_number)) {
                $prefix = PurchaseSetting::current()->grn_prefix ?: 'GRN';
                $receipt->receipt_number = $prefix.'-'.date('Ymd-His').'-'.random_int(10, 99);
            }
        });
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReceiptItem::class);
    }

    public function invoice(): HasMany
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
