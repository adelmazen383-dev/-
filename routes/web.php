<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// ─── Public Routes (with Rate Limiting) ────────────────────

Route::middleware('throttle:30,1')->group(function () {
    Route::get('/verify', [VerificationController::class, 'index'])->name('verify.index');
    Route::get('/telr/callback', [\App\Http\Controllers\InvoiceController::class, 'telrCallback'])->name('telr.callback');
    Route::get('/card/{token}', [\App\Http\Controllers\InvoiceController::class, 'publicBookingCard'])->name('booking-card.show');
    Route::get('/invoices/{invoice}/print', [\App\Http\Controllers\InvoiceController::class, 'invoicePrint'])->name('invoices.print');
    Route::get('/invoice/{token}', [\App\Http\Controllers\InvoiceController::class, 'publicInvoice'])->name('invoices.public');
});

Route::middleware('throttle:10,1')->group(function () {
    Route::get('/sign/{token}', [SignatureController::class, 'show'])->name('sign.show');
    Route::post('/sign/{token}', [SignatureController::class, 'store'])->name('sign.store');
    Route::post('/sign/{token}/reject', [SignatureController::class, 'reject'])->name('sign.reject');
});

// ─── Authenticated Admin Routes ────────────────────────────

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Invoices
    Route::resource('invoices', \App\Http\Controllers\InvoiceController::class)->except(['edit', 'update', 'destroy']);
    Route::get('/invoices/{invoice}/card', [\App\Http\Controllers\InvoiceController::class, 'bookingCard'])->name('invoices.booking-card');
    Route::post('/invoices/{invoice}/check-status', [\App\Http\Controllers\InvoiceController::class, 'checkStatus'])->name('invoices.check-status');
    Route::post('/invoices/{invoice}/retry-telr', [\App\Http\Controllers\InvoiceController::class, 'retryTelr'])->name('invoices.retry-telr');
    Route::post('/invoices/{invoice}/mark-as-paid', [\App\Http\Controllers\InvoiceController::class, 'markAsPaid'])->name('invoices.mark-as-paid');

    // Clients
    Route::resource('clients', ClientController::class);

    // Contracts
    Route::resource('contracts', ContractController::class)->except(['edit', 'update']);
    Route::get('/contracts/{contract}/edit', [ContractController::class, 'edit'])->name('contracts.edit');
    Route::put('/contracts/{contract}', [ContractController::class, 'update'])->name('contracts.update');
    Route::post('/contracts/{contract}/cancel', [ContractController::class, 'cancel'])->name('contracts.cancel');
    Route::post('/contracts/{contract}/send', [ContractController::class, 'send'])->name('contracts.send');
    Route::post('/contracts/{contract}/regenerate-pdf', [ContractController::class, 'regeneratePdf'])->name('contracts.regenerate-pdf');

    // User Management (Admin only)
    Route::middleware('can:manageUsers,App\Models\Contract')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle');
        Route::post('/users/{user}/toggle-template', [UserController::class, 'toggleTemplate'])->name('users.toggle-template');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Export
    Route::get('/export/contracts', [ExportController::class, 'contracts'])->name('export.contracts');
    Route::get('/export/customers', [ExportController::class, 'customers'])->name('export.customers');
});

require __DIR__ . '/auth.php';
