<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosOrder extends Model
{
    protected $fillable = [
        'order_number',
        'pos_session_id',
        'user_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_type',
        'tax_rate',
        'tax_amount',
        'total',
        'tendered_amount',
        'change_due',
        'payment_method',
        'payment_status',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'tendered_amount' => 'decimal:2',
            'change_due' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PosOrder $order) {
            if (empty($order->order_number)) {
                $order->order_number = 'POS-'.date('Ymd-His').'-'.random_int(10, 99);
            }
        });
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosOrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PosPayment::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(PosRefund::class);
    }

    public function refundedTotal(): float
    {
        return (float) $this->refunds()->where('status', 'completed')->sum('amount');
    }

    public static function createItem(
        self $order,
        ?Product $product,
        ?ProductVariant $variant,
        float $unitPrice,
        float $unitCost,
        int $quantity
    ): PosOrderItem {
        return PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $product?->id,
            'variant_id' => $variant?->id,
            'name' => $variant ? ($product?->name ?? '').' - '.$variant->display : ($product?->name ?? ''),
            'sku' => $variant?->sku ?? $product?->sku,
            'barcode' => $variant?->barcode ?? $product?->barcode,
            'unit_price' => $unitPrice,
            'unit_cost' => $unitCost,
            'quantity' => $quantity,
            'discount' => 0,
            'total_price' => round($unitPrice * $quantity, 2),
        ]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
