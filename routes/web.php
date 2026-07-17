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

Route::middleware('throttle:20,1')->group(function () {
    Route::get('/verify', [VerificationController::class, 'index'])->name('verify.index');
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

    // Clients
    Route::resource('clients', ClientController::class);

    // Contracts
    Route::resource('contracts', ContractController::class)->except(['edit', 'update', 'destroy']);
    Route::get('/contracts/{contract}/edit', [ContractController::class, 'edit'])->name('contracts.edit');
    Route::put('/contracts/{contract}', [ContractController::class, 'update'])->name('contracts.update');
    Route::post('/contracts/{contract}/cancel', [ContractController::class, 'cancel'])->name('contracts.cancel');

    // User Management (Admin only)
    Route::middleware('can:manageUsers,App\Models\Contract')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Export
    Route::get('/export/contracts', [ExportController::class, 'contracts'])->name('export.contracts');
    Route::get('/export/customers', [ExportController::class, 'customers'])->name('export.customers');
});

require __DIR__ . '/auth.php';
