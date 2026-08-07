<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseInvoiceItem extends Model
{
    protected $table = 'purchase_invoice_items';

    protected $fillable = [
        'purchase_invoice_id',
        'purchase_order_item_id',
        'purchase_receipt_item_id',
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

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
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
