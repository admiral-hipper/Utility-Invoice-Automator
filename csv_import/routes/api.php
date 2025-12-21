<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/auth/me', [\App\Http\Controllers\Api\AuthController::class, 'me']);
    Route::post('/auth/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);

    // ADMIN endpoints
    Route::middleware('ability:admin')->group(function () {
        // Import endpoints
        Route::get('/imports', [\App\Http\Controllers\Api\ImportController::class, 'index']);
        Route::get('/imports/{import}', [\App\Http\Controllers\Api\ImportController::class, 'show']);
        Route::post('/imports', [\App\Http\Controllers\Api\ImportController::class, 'store']);

        // Customer CRUD
        Route::apiResource('customers', App\Http\Controllers\Api\CustomerController::class);

        // Invoices endpoints
        Route::get('/invoices', [\App\Http\Controllers\Api\InvoiceController::class, 'index']);
        Route::get('/invoices/{invoice}', [\App\Http\Controllers\Api\InvoiceController::class, 'show']);
        Route::post('/invoices/{invoice}/issue', [\App\Http\Controllers\Api\InvoiceController::class, 'issue']);
        Route::post('/invoices/{invoice}/mark-paid', [\App\Http\Controllers\Api\InvoiceController::class, 'markPaid']);
        Route::get('/invoices/{invoice}/pdf', [\App\Http\Controllers\Api\InvoiceController::class, 'pdf']);
    });

    // USER endpoints
    Route::middleware('ability:customer')->group(function () {

        Route::get('/me/invoices', [\App\Http\Controllers\Api\InvoiceController::class, 'myIndex']);
        Route::get('/me/invoices/{invoice}', [\App\Http\Controllers\Api\InvoiceController::class, 'show']); // через policy
        Route::get('/me/invoices/{invoice}/pdf', [\App\Http\Controllers\Api\InvoiceController::class, 'pdf']); // через policy

        Route::get('/me/imports', [\App\Http\Controllers\Api\ImportController::class, 'myIndex']);
        Route::get('/me/imports/{import}', [\App\Http\Controllers\Api\ImportController::class, 'show']); // через policy
    });
});
