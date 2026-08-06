<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    protected $fillable = [
        'journal_number',
        'entry_date',
        'reference_type',
        'reference_id',
        'narration',
        'status',
        'reverses_id',
        'reversed_by_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (JournalEntry $entry) {
            if (empty($entry->journal_number)) {
                $entry->journal_number = static::nextNumber();
            }
        });
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversed_by_id');
    }

    public function reverses(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reverses_id');
    }

    public function totalDebit(): float
    {
        return (float) $this->lines()->sum('debit');
    }

    public function totalCredit(): float
    {
        return (float) $this->lines()->sum('credit');
    }

    public function isBalanced(): bool
    {
        return abs($this->totalDebit() - $this->totalCredit()) < 0.01;
    }

    public function isReversed(): bool
    {
        return $this->reversed_by_id !== null;
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function scopeOfReference($query, string $type, $id)
    {
        return $query->where('reference_type', $type)->where('reference_id', $id);
    }

    public static function nextNumber(): string
    {
        $date = now()->format('Ymd');
        $last = static::query()->where('journal_number', 'like', "JV-{$date}-%")
            ->orderByDesc('journal_number')->value('journal_number');

        $seq = $last ? ((int) substr($last, strrpos($last, '-') + 1)) + 1 : 1;

        return 'JV-'.$date.'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
