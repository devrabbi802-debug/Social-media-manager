<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'customer_id', 'customer_name', 'customer_phone', 'order_number', 'status', 'total', 'subtotal',
        'shipping_cost', 'tax', 'discount', 'payment_method',
        'payment_status', 'carrier', 'tracking_id', 'estimated_delivery',
        'tracking_steps', 'notes', 'shipping_address_id',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'tax' => 'decimal:2',
            'discount' => 'decimal:2',
            'estimated_delivery' => 'datetime',
            'tracking_steps' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'shipping_address_id');
    }
}
