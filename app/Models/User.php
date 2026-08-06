<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'phone', 'company', 'password', 'locale', 'type', 'role_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * User with no role is the tenant super admin (full access).
     */
    public function isSuperAdmin(): bool
    {
        return $this->role_id === null;
    }

    public function hasPermission(string $module, string $action): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->role?->hasPermission($module, $action) ?? false;
    }

    /**
     * All granted "module.action" strings (super admin => every registered module).
     */
    public function permissionList(): array
    {
        if ($this->isSuperAdmin()) {
            $all = [];
            foreach (config('tenant-permissions.groups') ?? [] as $group) {
                foreach ($group['items'] as $item) {
                    foreach ($item['permissions'] as $perm) {
                        $all[] = $item['slug'].'.'.$perm;
                    }
                }
            }

            return $all;
        }

        return $this->role?->permissionList() ?? [];
    }

    public function businessSetting(): HasOne
    {
        return $this->hasOne(BusinessSetting::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }
}
