<?php

namespace Database\Seeders;

use App\Models\InvoiceCounter;
use Illuminate\Database\Seeder;

class InvoiceCounterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $periods = [
            now()->subMonth()->format('Y-m'),
            now()->format('Y-m'),
        ];

        foreach ($periods as $p) {
            InvoiceCounter::query()->firstOrCreate(
                ['period' => $p],
                ['next_number' => 1]
            );
        }
    }
}
