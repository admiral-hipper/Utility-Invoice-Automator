<?php

use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [\App\Http\Controllers\Api\AuthController::class, 'login'])->name('login');
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/auth/me', [\App\Http\Controllers\Api\AuthController::class, 'me']);
    Route::post('/auth/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);

    // ADMIN endpoints
    Route::middleware('ability:admin')->group(function () {
        // Import endpoints
        Route::get('/imports', [\App\Http\Controllers\Api\ImportController::class, 'index']);
        Route::get('/imports/{import}', [\App\Http\Controllers\Api\ImportController::class, 'show']);
        Route::get('/imports/{import}/download', [\App\Http\Controllers\Api\ImportController::class, 'download'])->name('download-import');
        Route::post('/imports', [\App\Http\Controllers\Api\ImportController::class, 'store']);

        // Customer CRUD
        Route::apiResource('customers', App\Http\Controllers\Api\CustomerController::class);

        // Invoices endpoints

        Route::get('/invoices', [\App\Http\Controllers\Api\InvoiceController::class, 'index'])
            ->can('viewAny', \App\Models\Invoice::class);

        Route::get('/invoices/{invoice}', [\App\Http\Controllers\Api\InvoiceController::class, 'show'])
            ->can('view', 'invoice');

        Route::get('/invoices/{invoice}/pdf', [\App\Http\Controllers\Api\InvoiceController::class, 'pdf'])
            ->can('view', 'invoice');

        Route::post('/invoices/{invoice}/issue', [\App\Http\Controllers\Api\InvoiceController::class, 'issue'])
            ->can('update', 'invoice');

        Route::post('/invoices/{invoice}/mark-paid', [\App\Http\Controllers\Api\InvoiceController::class, 'markPaid'])
            ->can('update', 'invoice');
    });

    // USER endpoints
    Route::middleware('ability:customer')->group(function () {
        Route::get('/me/customers', [App\Http\Controllers\Api\CustomerController::class, 'index'])->can('viewAny', App\Models\Customer::class);
        Route::get('/me/customers/{customer}', [App\Http\Controllers\Api\CustomerController::class, 'show'])->can('view', 'customer');
        Route::get('/me/invoices', [\App\Http\Controllers\Api\InvoiceController::class, 'myIndex'])->can('viewAny', \App\Models\Invoice::class);
        Route::can('view', 'invoice')->group(function () {
            Route::get('/me/invoices/{invoice}', [\App\Http\Controllers\Api\InvoiceController::class, 'show']);
        });
    });
});
