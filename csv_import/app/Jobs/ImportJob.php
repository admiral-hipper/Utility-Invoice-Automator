<?php

namespace App\Jobs;

use App\DTOs\ImportRowDTO;
use App\Enums\ImportStatus;
use App\Enums\InvoiceStatus;
use App\Enums\Service;
use App\Exceptions\ImportException;
use App\Models\Customer;
use App\Models\Import;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\Import\ImportManager;
use App\Services\Invoice\InvoiceNumberService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
    public function handle(ImportManager $imports): void
    {
        $filepath = Storage::disk('import')->path($this->import->file_path);
        $importer = $imports->importerFor($filepath);
        Log::debug("Import file {$this->import->file_path} started");
        try {
            $counter = 0;
            foreach ($importer->getRows($filepath) as $row) {
                $counter++;
                $period = Carbon::now()->format('Y-m');
                $dueDate = Carbon::now()->addDays(10);
                $customerId = Customer::query()
                    ->where('phone', '=', $row->phone)->where('email', '=', $row->email)
                    ->firstOr(callback: fn() => throw new ImportException("Cannot find Customer record for {$row->full_name}({$row->email}, {$row->phone})"));

                [$invoiceNo, $paymentRef] = app(InvoiceNumberService::class)->nextInvoiceNo($period); // '2025-12'

                $invoice = Invoice::factory([
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
                ])->create();
                $this->createInvoiceItems($invoice, $row);
                InvoiceJob::dispatch($invoice);
            }

            $this->import->status = ImportStatus::PROCESSED;
            $this->import->total_rows = $counter;
            $this->import->save();
            Log::debug("Import file {$this->import->file_path} finished");
        } catch (ImportException $e) {
            $this->import->status = ImportStatus::FAILED;
            $this->import->errors = $e->getMessage();
            $this->import->save();
            Log::emergency('Import failed with message: ' . $e->getMessage());
            throw $e;
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
                'amount' => $row->{$service->value}
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
