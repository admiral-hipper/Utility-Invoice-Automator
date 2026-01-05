<?php

namespace Tests\Feature\Jobs;

use App\Enums\ImportStatus;
use App\Jobs\ImportJob;
use App\Jobs\InvoiceJob;
use App\Models\Customer;
use App\Models\Import;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_job_processes_valid_csv_and_updates_import_stats(): void
    {
        Storage::fake('import');
        Bus::fake([InvoiceJob::class]);

        User::factory()->has(Customer::factory([
            'email' => 'example1@gmail.com',
            'phone' => '+40720100101',
        ]))->create();

        User::factory()->has(Customer::factory([
            'email' => 'example2@gmail.com',
            'phone' => '+40720100102',
        ]))->create();

        $csv = implode("\n", [
            'full_name,email,phone,house_address,apartment,gas,electricity,heating,territory,water,currency',
            'Andrei Popescu,example1@gmail.com,+40720100101,Str. Lalelelor 12,10,120.50,85.20,210.00,32.00,55.10,RON',
            'Ioana Ionescu,example2@gmail.com,+40720100102,Str. Lalelelor 12,11,98.30,74.00,190.00,32.00,49.80,RON',
        ])."\n";

        $path = 'csv/test_2025-12.csv';
        Storage::disk('import')->put($path, $csv);

        $import = Import::query()->create([
            'period' => '2025-12',
            'file_path' => $path,
            'rows_total' => 0,
            'status' => ImportStatus::QUEUED,
        ]);

        ImportJob::dispatchSync($import);

        $import->refresh();

        $this->assertNotEquals('queued', $import->status, 'Import status should change after processing.');

        $this->assertEquals(2, (int) $import->total_rows);

        if (class_exists(\App\Models\Invoice::class) && Schema::hasTable('invoices')) {
            $this->assertGreaterThanOrEqual(1, \App\Models\Invoice::query()->count());
        }

        if (class_exists(\App\Models\Customer::class) && Schema::hasTable('customers')) {
            $this->assertGreaterThanOrEqual(1, \App\Models\Customer::query()->count());
        }
    }

    public function test_import_job_marks_failed_rows_when_csv_has_invalid_data(): void
    {
        Storage::fake('import');

        User::factory()->has(Customer::factory([
            'email' => 'example1@gmail.com',
            'phone' => '+40720100101',
        ]))->create();

        // Second is wrong
        $csv = implode("\n", [
            'full_name,email,phone,house_address,apartment,gas,electricity,heating,territory,water,currency',
            'Andrei Popescu,example1@gmail.com,+40720100101,Str. Lalelelor 12,10,120.50,85.20,210.00,32.00,55.10,RON',
            ',,,Str. Lalelelor 12,,foo,bar,,,,', // Trash
        ])."\n";

        $path = 'test_invalid_2025-12.csv';
        Storage::disk('import')->put($path, $csv);

        $import = Import::query()->create([
            'file_path' => 'test_invalid_2025-12.csv',
            'period' => '2025-12',
            'status' => ImportStatus::QUEUED,
        ]);

        try {
            ImportJob::dispatchSync($import);
            $this->fail('Job should be failed');
        } catch (\Throwable $e) {
            $import->refresh();

            $this->assertEquals(ImportStatus::FAILED->value, $import->status);

            $this->assertEquals('Value of full_name is empty! (ID:{2)', $import->errors);
        }
    }
}
