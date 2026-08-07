<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPaymentMethod extends Model
{
    protected $fillable = [
        'supplier_payment_id',
        'method',
        'amount',
        'reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(SupplierPayment::class, 'supplier_payment_id');
    }

    public function methodName(): string
    {
        $account = ChartOfAccount::byCode($this->method);

        return $account?->name ?? ucfirst(str_replace('_', ' ', $this->method));
    }
}
