<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    public const TYPES = [
        'asset' => 'Assets',
        'liability' => 'Liabilities',
        'equity' => 'Equity',
        'income' => 'Income',
        'expense' => 'Expenses',
    ];

    protected $fillable = [
        'parent_id',
        'account_type',
        'code',
        'name',
        'description',
        'normal_balance',
        'is_system',
        'is_active',
        'is_pos_payment',
        'opening_balance',
        'opening_balance_date',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
            'is_pos_payment' => 'boolean',
            'opening_balance' => 'decimal:2',
            'opening_balance_date' => 'date',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->account_type] ?? ucfirst($this->account_type);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePosPayment($query)
    {
        return $query->where('is_pos_payment', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('account_type', $type);
    }

    public static function byCode(string $code): ?self
    {
        return static::query()->where('code', $code)->first();
    }
}
