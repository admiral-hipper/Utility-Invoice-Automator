<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin (фиксированные креды для демо)
        User::query()->firstOrCreate(
            ['email' => 'admin@demo.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('secret123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Customers users
        User::factory()->count(5)->create(['role' => 'customer']);
    }
}
