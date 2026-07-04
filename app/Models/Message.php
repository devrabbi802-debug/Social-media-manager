<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'direction',
        'type',
        'content',
        'image_path',
        'image_analysis',
        'facebook_mid',
    ];

    protected function casts(): array
    {
        return [
            'image_analysis' => 'array',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    public function isIncoming(): bool
    {
        return $this->direction === 'incoming';
    }
}
