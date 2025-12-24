<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ImportResource;
use App\Jobs\ImportJob;
use App\Models\Import;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResource
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min($perPage, 100)); // clamp

        $imports = Import::query()
            ->latest()
            ->paginate($perPage);
        return $imports->toResourceCollection();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResource
    {
        $file = $request->file('import_file');
        $filename = 'Import_' . Carbon::now()->format('Y-m-d-His') . '.csv';
        Storage::disk('imports')->put($filename, $file->get());
        $filepath = Storage::disk('imports')->path($filename);
        $import = Import::factory([
            'period' => Carbon::now()->format('Y-m'),
            'file_path' => $filepath
        ])->create();
        ImportJob::dispatch($import);
        return $import->toResource();
    }

    /**
     * Display the specified resource.
     */
    public function show(Import $invoice)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResource
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        //
    }
}
