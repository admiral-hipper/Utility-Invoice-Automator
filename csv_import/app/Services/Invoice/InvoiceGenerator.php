<?php

namespace App\Services\Invoice;

use App\DTOs\BillingDTO;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDFPDF;

class InvoiceGenerator
{
    public function __construct(
        protected Invoice $invoice,
        protected BillingDTO $billing,
        protected string $qrBase64
    ) {}

    public function generate(): DomPDFPDF
    {
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $this->invoice,
            'billing' => $this->billing,
            'paymentRef' => $this->invoice->payment_ref,
            'qrBase64' => $this->qrBase64,
        ])->setPaper('a4');
        return $pdf;
    }
}
