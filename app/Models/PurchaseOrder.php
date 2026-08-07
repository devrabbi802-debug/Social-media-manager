<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    public const STATUSES = [
        'draft' => 'Draft',
        'ordered' => 'Ordered',
        'partially_received' => 'Partially Received',
        'received' => 'Received',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'po_number',
        'supplier_id',
        'order_date',
        'expected_date',
        'status',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'total',
        'notes',
        'terms',
        'created_by',
        'ordered_at',
        'received_at',
        'cancelled_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'ordered_at' => 'datetime',
        'received_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (PurchaseOrder $order) {
            if (empty($order->po_number)) {
                $prefix = PurchaseSetting::current()->po_prefix ?: 'PO';
                $order->po_number = $prefix.'-'.date('Ymd-His').'-'.random_int(10, 99);
            }
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(PurchaseReceipt::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    public function advancePayments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class)->where('type', 'advance')->where('status', '!=', 'cancelled');
    }

    public function advanceTotal(): float
    {
        return round((float) $this->advancePayments->sum('amount'), 2);
    }

    public function remainingAdvance(): float
    {
        $applied = (float) $this->invoices->sum('advance_applied');

        return round($this->advanceTotal() - $applied, 2);
    }

    /**
     * Maximum advance that can still be paid against this PO.
     */
    public function maxAdvanceable(): float
    {
        $allowed = (float) $this->total - $this->advanceTotal();

        return round(max($allowed, 0), 2);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Whether all ordered quantities have been received.
     */
    public function fullyReceived(): bool
    {
        if ($this->items->isEmpty()) {
            return false;
        }

        return $this->items->every(fn ($item) => $item->received_quantity >= $item->quantity);
    }

    public function totalReceivedQty(): int
    {
        return $this->items->sum('received_quantity');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function isReceivable(): bool
    {
        return in_array($this->status, ['ordered', 'partially_received'], true);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (! $search) {
            return $query;
        }

        return $query->where('po_number', 'like', "%{$search}%");
    }
}
