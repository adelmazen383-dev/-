<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\VerificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Verification
Route::get('/verify', [VerificationController::class, 'index'])->name('verify.index');

// Public Signature (Customer Side)
Route::get('/sign/{token}', [SignatureController::class, 'show'])->name('sign.show');
Route::post('/sign/{token}', [SignatureController::class, 'store'])->name('sign.store');
Route::post('/sign/{token}/reject', [SignatureController::class, 'reject'])->name('sign.reject');

// Admin / Dashboard Routes
Route::middleware(['auth'])->group(function () {
    
    Route::get('/dashboard', function () {
        return view('dashboard'); // Assuming you have a basic dashboard view
    })->name('dashboard');

    // Clients
    Route::resource('clients', ClientController::class);
    
    // Contracts
    Route::resource('contracts', ContractController::class)->except(['edit', 'update', 'destroy']);
    Route::post('/contracts/{contract}/cancel', [ContractController::class, 'cancel'])->name('contracts.cancel');
    Route::post('/contracts/{contract}/resend-whatsapp', [ContractController::class, 'resendWhatsapp'])->name('contracts.resend_whatsapp');

    // Templates Management (Placeholder route)
    Route::get('/templates', function () {
        return "صفحة إدارة القوالب (للمستقبل)";
    })->name('templates.index');
});

// require __DIR__.'/auth.php'; // Uncomment if using Laravel Breeze
