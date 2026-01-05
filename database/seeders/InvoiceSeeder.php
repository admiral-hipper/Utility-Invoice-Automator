<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Import;
use App\Models\Invoice;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $imports = Import::query()->orderByDesc('id')->get();
        $customers = Customer::query()->get();

        foreach ($customers as $customer) {
            foreach ($imports->take(2) as $import) {
                //
                $invoice = Invoice::factory()->create([
                    'customer_id' => $customer->id,
                    'import_id' => $import->id,
                    'period' => $import->period,
                    'currency' => 'RON',
                    'status' => 'draft',
                    'issued_at' => null,
                    'due_date' => now()->addDays((int) config('billing.due_days', 10))->toDateString(),
                ]);

                //
                $invoice->update([
                    'total' => 0,
                ]);
            }
        }
    }
}
