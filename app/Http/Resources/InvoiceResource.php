<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,

            'invoice_no'   => $this->invoice_no,
            'payment_ref'  => $this->payment_ref,

            'customer_id'  => $this->customer_id,
            'import_id'    => $this->import_id,

            'period'       => $this->period,
            'currency'     => $this->currency,

            // Keep both numeric + formatted to avoid frontend float issues
            'total' => [
                'value'     => $this->total === null ? null : (string) $this->total, // decimal-safe
                'formatted' => $this->total === null ? null : number_format((float) $this->total, 2, '.', ''),
            ],

            // Dates (ISO) + unix timestamps (handy for UI)
            'due_date' => [
                'date'      => $this->due_date,   // YYYY-MM-DD
                'timestamp' =>   Carbon::createFromFormat('Y-m-d', $this->due_date)->timestamp,
            ],

            'sent_at' => [
                'date'      => $this->sent_at?->toDateString(),
                'timestamp' => $this->sent_at?->startOfDay()->timestamp,
            ],

            'issued_at' => [
                'datetime'  => $this->issued_at?->toISOString(),   // ISO-8601
                'timestamp' => $this->issued_at?->timestamp,
            ],

            'status' => $this->status, // 'draft'|'paid'|'issued'|'canceled'

            'pdf' => [
                'download_link' => route('invoice.pdf.download', ['invoice' => $this]),
                'base64' => !empty($this->pdf_path) ? base64_encode(Storage::disk('invoices')->exists($this->pdf_path)
                    ? Storage::disk('invoices')->get($this->pdf_path) : 'empty') : 'empty'
            ],

            'created_at' => [
                'datetime'  => $this->created_at?->toISOString(),
                'timestamp' => $this->created_at?->timestamp,
            ],
            'updated_at' => [
                'datetime'  => $this->updated_at?->toISOString(),
                'timestamp' => $this->updated_at?->timestamp,
            ],
        ];
    }
}
