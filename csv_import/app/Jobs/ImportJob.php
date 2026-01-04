<?php

namespace App\Jobs;

use App\DTOs\ImportRowDTO;
use App\Enums\InvoiceStatus;
use App\Enums\Service;
use App\Exceptions\ImportException;
use App\Models\Customer;
use App\Models\Import;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\Invoice\InvoiceNumberService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use ZipArchive;

class ImportJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private Import $import) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $csv = Reader::from(Storage::disk('import')->path($this->import->file_path), 'r')->setHeaderOffset(0);

        foreach ($csv->getRecords() as $id => $record) {
            $row = ImportRowDTO::create(array_merge(['id' => $id], $record));
            $period = Carbon::now()->format('Y-m');
            $dueDate = Carbon::now()->addDays(10);
            $customerId = Customer::query()
                ->where('phone', $row->phone)->where('email', $row->email)->first();
            [$invoiceNo, $paymentRef] = app(InvoiceNumberService::class)->nextInvoiceNo($period); // '2025-12'

            $invoice = Invoice::create([
                'customer_id' => $customerId,
                'import_id'   => $this->import->id,
                'period'      => $period,

                'invoice_no'  => $invoiceNo,
                'payment_ref' => $paymentRef,

                'currency'    => 'RON',
                'due_date'    => $dueDate,

                'status'      => InvoiceStatus::DRAFT,
                'issued_at'   => null,

                'total'    => $this->calcSubTotal($row),
            ]);
            $this->createInvoiceItems($invoice, $row);
            InvoiceJob::dispatch($invoice);
        }
        $this->import->file_path = $this->zipImport($this->import->file_path);
        $this->import->save();
    }

    protected function validateRow(ImportRowDTO $row): void
    {
        foreach ($row->toArray() as $column => $value) {
            if (empty($value)) {
                throw new ImportException("Value of $column is empty! (ID:{$row->id})");
            }
        }
        if (!filter_var($row->email, FILTER_VALIDATE_EMAIL)) {
            throw new ImportException("Value of email is invalid! (ID:{$row->id})");
        }
    }

    protected function calcSubTotal(ImportRowDTO $row): float
    {
        return $row->gas + $row->electricity + $row->water + $row->territory + $row->heating;
    }

    protected function createInvoiceItems(Invoice $invoice, ImportRowDTO $row): void
    {
        foreach (Service::cases() as $service) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service' => $service->value,
                'amount' => $row->$service->value
            ]);
        }
    }

    protected function zipImport(string $filepath): string
    {
        $zip = new ZipArchive();
        $filename = 'ARCHIVED_' . basename($filepath) . '.zip';
        $zipFilePath = Storage::disk('archival')->path($filename);
        $zip->open($zipFilePath);
        $zip->addFile($filepath, basename($filepath));
        $zip->close();
        return $zipFilePath;
    }
}
