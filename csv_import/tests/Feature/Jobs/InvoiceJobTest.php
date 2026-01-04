<?php

namespace Tests\Feature;

use App\Jobs\InvoiceJob;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use App\Services\Invoice\InvoiceGenerator;
use App\Services\Storage\CustomerStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class InvoiceJobTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_invoice_job_stores_pdf_and_sets_pdf_path(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory(['user_id' => $user->id])->create();
        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'period' => '2025-12',
            'invoice_no' => 'INV-202512-000001',
            'payment_ref' => 'PAY:INV-202512-000001',
            'currency' => 'RON',
            'total' => 123.45,
            'pdf_path' => null,
        ]);

        $pdfMock = Mockery::mock();
        $pdfMock->shouldReceive('output')->once()->andReturn('%PDF-FAKE%');

        $generatorOverload = Mockery::mock('overload:' . InvoiceGenerator::class);
        $generatorOverload->shouldReceive('__construct')->once(); // конструктор вызовется
        $generatorOverload->shouldReceive('generate')->once()->andReturn($pdfMock);

        $expectedFilename = '2025-12/invoice-INV-202512-000001.pdf';
        $expectedPath = "invoices/{$customer->id}/{$expectedFilename}";

        $storageOverload = Mockery::mock('overload:' . CustomerStorage::class);
        $storageOverload->shouldReceive('__construct')->once();

        $storageOverload->shouldReceive('putToUserDir')
            ->once()
            ->withArgs(function ($custArg, $filenameArg, $contentsArg) use ($customer, $expectedFilename) {
                return (int)$custArg->id === (int)$customer->id
                    && $filenameArg === $expectedFilename
                    && $contentsArg === '%PDF-FAKE%';
            })
            ->andReturnTrue();

        $storageOverload->shouldReceive('path')
            ->once()
            ->withArgs(function ($custArg, $filenameArg) use ($customer, $expectedFilename) {
                return (int)$custArg->id === (int)$customer->id
                    && $filenameArg === $expectedFilename;
            })
            ->andReturn($expectedPath);

        (new InvoiceJob($invoice))->handle();

        $invoice->refresh();
        $this->assertEquals($expectedPath, $invoice->pdf_path);
    }
}
