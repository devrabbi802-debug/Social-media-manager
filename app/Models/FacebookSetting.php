<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacebookSetting extends Model
{
    protected $fillable = [
        'user_id',
        'app_id',
        'app_secret',
        'verify_token',
        'page_id',
        'page_access_token',
        'ai_auto_reply_enabled',
    ];

    protected $hidden = [
        'app_secret',
        'page_access_token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
