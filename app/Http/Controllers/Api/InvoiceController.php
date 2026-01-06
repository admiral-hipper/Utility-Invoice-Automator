<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Services\Storage\CustomerStorage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the invoice.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min($perPage, 100)); // clamp

        $imports = Invoice::query()
            ->latest()
            ->paginate($perPage);

        return $imports->toResourceCollection();
    }

    /** Display a listing of  */
    public function myIndex(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min($perPage, 100)); // clamp

        $imports = Invoice::query()->whereIn('customer_id', $request->user()->customers->pluck('id'))
            ->latest()
            ->paginate($perPage);

        return $imports->toResourceCollection();
    }

    public function download(Invoice $invoice): StreamedResponse
    {
        return Storage::disk('invoices')->download($invoice->pdf_path, basename($invoice->pdf_path), ['Content-Type' => 'application/pdf']);
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        return $invoice->toResource();
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice)
    {
        //
    }

    /**
     * Update the specified invoice in storage.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        //
    }

    /**
     * Remove the specified invoice from storage.
     */
    public function destroy(Invoice $invoice)
    {
        //
    }
}
