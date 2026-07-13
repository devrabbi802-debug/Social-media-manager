<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacebookSetting extends Model
{
    protected $fillable = [
        'user_id',
        'connection_type',
        'app_id',
        'app_secret',
        'verify_token',
        'page_id',
        'page_name',
        'page_access_token',
        'ai_auto_reply_enabled',
        'zernio_api_key',
        'zernio_account_id',
        'zernio_profile_id',
    ];

    protected $hidden = [
        'app_secret',
        'page_access_token',
        'zernio_api_key',
    ];

    /**
     * Check if connected via Zernio.
     */
    public function isZernio(): bool
    {
        return $this->connection_type === 'zernio';
    }

    /**
     * Check if connected via Facebook App (direct).
     */
    public function isFacebookApp(): bool
    {
        return $this->connection_type === 'facebook_app' || $this->connection_type === null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
