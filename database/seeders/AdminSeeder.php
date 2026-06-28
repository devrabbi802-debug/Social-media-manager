<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'admin@socialboost.com'],
            [
                'name' => 'Master Admin',
                'password' => Hash::make('Admin@123456'),
                'role' => 'super_admin',
            ]
        );
    }
}
