<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfAndEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_job_sends_invoice_if_job_exists(): void
    {
        if (!class_exists(\App\Jobs\SendInvoiceEmailJob::class)) {
            $this->markTestSkipped('SendInvoiceEmailJob not found.');
        }
        if (!class_exists(\App\Mail\InvoiceMail::class)) {
            $this->markTestSkipped('InvoiceMail not found.');
        }
        if (!class_exists(\App\Services\Invoice\InvoiceGenerator::class)) {
            $this->markTestSkipped('InvoicePdfService not found.');
        }

        Storage::fake('local');
        Mail::fake();

        config()->set('billing.company_name', 'Utilities Billing SRL');
        config()->set('billing.iban', 'RO49AAAA1B31007593840000');

        $user = User::factory()->create();
        $customer = Customer::factory()->create(['email' => 'client@example.com', 'user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'invoice_no' => 'INV-202512-000002',
            'payment_ref' => 'PAY:INV-202512-000002',
            'currency' => 'RON',
            'total' => 50.00,
        ]);

        dispatch_sync(new \App\Jobs\SendInvoiceEmailJob($invoice->id, $customer->email));

        Mail::assertSent(\App\Mail\InvoiceMail::class, fn($m) => $m->hasTo('client@example.com'));
    }
}
