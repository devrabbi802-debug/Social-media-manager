<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $isTenant = tenant() !== null;

        if ($isTenant) {
            $this->call(ClothingInventorySeeder::class);
        } else {
            $this->call(AdminSeeder::class);
            $this->call(BusinessCategorySeeder::class);
        }
    }
}
