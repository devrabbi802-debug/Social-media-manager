<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BusinessSetup extends Model
{
    protected $fillable = [
        'business_name',
        'logo_path',
        'support_number',
        'support_email',
    ];

    public static function getActive(): self
    {
        return static::firstOrCreate([]);
    }

    public function getLogoUrl(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
    }
}
