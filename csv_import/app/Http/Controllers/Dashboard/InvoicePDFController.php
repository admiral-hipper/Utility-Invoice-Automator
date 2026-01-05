<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoicePDFController extends Controller
{
    use AuthorizesRequests;

    public function download(Invoice $invoice): StreamedResponse
    {
        $this->authorize('view', $invoice);

        return Storage::disk('invoices')->download($invoice->pdf_path, basename($invoice->pdf_path), ['Content-Type' => 'application/pdf']);
    }

    public function show(Invoice $invoice): StreamedResponse
    {
        Gate::authorize('view', $invoice);
        if (! $invoice->pdf_path) {
            abort(404);
        }

        return response()->stream(function () use ($invoice) {
            $stream = Storage::disk('invoices')->readStream($invoice->pdf_path);

            if ($stream === false) {
                abort(404);
            }

            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.basename($invoice->pdf_path).'"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'public',
        ]);
    }
}
