<?php

namespace Database\Seeders;

use App\Enums\Service;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Seeder;

class InvoiceItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $invoices = Invoice::query()->get();

        foreach ($invoices as $inv) {
            $sum = 0.0;

            foreach (Service::cases() as $service) {
                $amount = random_int(1000, 30000) / 100; // 10.00..300.00
                InvoiceItem::query()->create([
                    'invoice_id' => $inv->id,
                    'service' => $service,
                    'amount' => $amount,
                ]);
                $sum += $amount;
            }

            $sum = round($sum, 2);

            $inv->update([
                'total' => $sum,
            ]);
        }
    }
}
