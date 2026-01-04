<?php

namespace Tests\Unit;

use App\Services\Invoice\InvoiceNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceNumberServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_unique_invoice_no_and_payment_ref(): void
    {
        config()->set('billing.payment_ref_prefix', 'PAY');

        $svc = app(InvoiceNumberService::class);

        [$no1, $ref1] = $svc->nextInvoiceNo('2025-12');
        [$no2, $ref2] = $svc->nextInvoiceNo('2025-12');

        $this->assertMatchesRegularExpression('/^INV-202512-\d{6}$/', $no1);
        $this->assertMatchesRegularExpression('/^INV-202512-\d{6}$/', $no2);
        $this->assertNotEquals($no1, $no2);

        $this->assertEquals('PAY:' . $no1, $ref1);
        $this->assertEquals('PAY:' . $no2, $ref2);
    }
}
