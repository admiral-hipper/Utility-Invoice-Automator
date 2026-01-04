<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Import;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $period = now()->format('Y-m');

        return [
            'customer_id' => Customer::factory(),
            'import_id' => Import::factory(),
            'period' => $period,

            'invoice_no' => 'INV-' . now()->format('Ym') . '-' . str_pad((string) $this->faker->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'payment_ref' => 'PAY:INV-' . now()->format('Ym') . '-000001',

            'currency' => 'RON',
            'due_date' => now()->addDays(10)->toDateString(),
            'issued_at' => null,

            'status' => 'draft', // draft|issued|paid|canceled
            'total' => 100.00,

            'pdf_path' => null,
        ];
    }
}
