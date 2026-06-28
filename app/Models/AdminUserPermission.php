<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminUserPermission extends Model
{
    protected $fillable = [
        'admin_id',
        'menu_slug',
        'permission',
    ];
}
