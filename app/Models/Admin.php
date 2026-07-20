<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function permissions()
    {
        return $this->hasMany(AdminUserPermission::class);
    }

    public function hasPermission(string $menuSlug, string $permission): bool
    {
        if ($this->role === 'super_admin') {
            return true;
        }

        return $this->permissions()
            ->where('menu_slug', $menuSlug)
            ->where('permission', $permission)
            ->exists();
    }

    public function getAllPermissions(): array
    {
        if ($this->role === 'super_admin') {
            $all = [];
            foreach (config('menu.groups') ?? [] as $group) {
                foreach ($group['items'] as $item) {
                    foreach ($item['permissions'] as $perm) {
                        $all[] = $item['slug'] . '.' . $perm;
                    }
                }
            }
            return $all;
        }

        return $this->permissions->pluck('permission', 'menu_slug')
            ->flatMap(fn($perms, $slug) => collect($perms)->map(fn($p) => $slug . '.' . $p))
            ->toArray();
    }

    public function getPermissionsBySlug(string $menuSlug): array
    {
        if ($this->role === 'super_admin') {
            return ['list', 'create', 'edit', 'delete', 'view'];
        }

        return $this->permissions()
            ->where('menu_slug', $menuSlug)
            ->pluck('permission')
            ->toArray();
    }
}
