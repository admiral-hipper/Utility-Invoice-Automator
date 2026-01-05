<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->where('role', UserRole::CUSTOMER->value)->get();

        foreach ($users as $u) {
            Customer::factory()->forUser($u)->create();
        }
    }
}
