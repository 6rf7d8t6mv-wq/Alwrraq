<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->firstOrCreate([
            'phone' => '0500000000',
        ], [
            'name' => 'مدير النظام',
            'password' => 'admin12345',
            'role' => 'admin',
        ]);
    }
}
