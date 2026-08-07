<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReceiptItem extends Model
{
    protected $fillable = [
        'purchase_receipt_id',
        'purchase_order_item_id',
        'product_id',
        'variant_id',
        'name',
        'sku',
        'quantity',
        'unit_cost',
        'discount',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
