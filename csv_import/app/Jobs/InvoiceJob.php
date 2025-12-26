<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\Invoice\InvoiceGenerator;
use App\Services\Storage\CustomerStorage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InvoiceJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private Invoice $invoice)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->invoice->loadMissing(['customer', 'items', 'import']);
        $paymentRef = $this->invoice->payment_ref;

        $billing = config('billing');
        $qrPayload = implode("\n", [
            "IBAN=" . ($billing['iban'] ?? ''),
            "AMOUNT=" . number_format((float) $this->invoice->total, 2, '.', ''),
            "CURRENCY=" . ($invoice->currency ?? 'RON'),
            "REFERENCE=" . $paymentRef,
            "BENEFICIARY=" . ($billing['company_name'] ?? ''),
        ]);

        $qrPng = QrCode::format('png')->size(220)->margin(1)->generate($qrPayload);
        $qrBase64 = 'data:image/png;base64,' . base64_encode($qrPng);

        $filename =  $this->invoice->period . '/' . "invoice-{$this->invoice->invoice_no}.pdf";
        $generator = new InvoiceGenerator($this->invoice, $billing, $qrBase64);
        $contents = $generator->generate()->output();
        $storage = (new CustomerStorage());
        $storage->putToUserDir(
            $this->invoice->customer,
            $filename,
            $contents,
        );
        $this->invoice->pdf_path = $storage->path(
            $this->invoice->customer,
            $filename
        );
        $this->invoice->save();
    }
}
