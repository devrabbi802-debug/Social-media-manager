<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $table = 'tenant_roles';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
        'is_system',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_system' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    /**
     * List of granted "module.action" strings.
     */
    public function permissionList(): array
    {
        return array_values(array_filter($this->permissions ?? []));
    }

    public function hasPermission(string $module, string $action): bool
    {
        return in_array("{$module}.{$action}", $this->permissionList(), true);
    }
}
