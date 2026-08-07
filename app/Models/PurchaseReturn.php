<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'return_number',
        'purchase_receipt_id',
        'purchase_invoice_id',
        'supplier_id',
        'warehouse_id',
        'return_date',
        'status',
        'total',
        'reason',
        'created_by',
        'completed_at',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (PurchaseReturn $return) {
            if (empty($return->return_number)) {
                $prefix = PurchaseSetting::current()->rtn_prefix ?: 'RTN';
                $return->return_number = $prefix.'-'.date('Ymd-His').'-'.random_int(10, 99);
            }
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseReceipt(): BelongsTo
    {
        return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id');
    }

    public function purchaseInvoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
