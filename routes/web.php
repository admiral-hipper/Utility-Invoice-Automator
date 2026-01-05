<?php

use App\Http\Controllers\Dashboard\InvoicePDFController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', fn () => response()->json(['ok' => true]));

Route::get('/invoices/{invoice}/download', [InvoicePDFController::class, 'download'])->name('invoice.pdf.download');
Route::get('/invoices/{invoice}/show', [InvoicePDFController::class, 'show'])->name('invoice.pdf.show');
