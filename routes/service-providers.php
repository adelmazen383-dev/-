<?php

use App\Http\Controllers\Admin\ServiceProviderAdminController;
use App\Http\Controllers\Provider\AuthController as ProviderAuthController;
use App\Http\Controllers\Provider\DashboardController as ProviderDashboardController;
use App\Http\Controllers\Provider\InvoiceController as ProviderInvoiceController;
use App\Http\Controllers\Provider\PublicPaymentController as ProviderPublicPaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Service Providers Portal Routes (Completely Isolated from Core Site)
|--------------------------------------------------------------------------
*/

// ─── 1. Public Customer Payment Portal for Service Provider Invoices ───
Route::prefix('p-pay')->name('provider-pay.')->middleware('throttle:30,1')->group(function () {
    Route::get('/telr/callback', [ProviderPublicPaymentController::class, 'telrCallback'])->name('telr.callback');
    Route::match(['get', 'post'], '/tamara/callback', [ProviderPublicPaymentController::class, 'tamaraCallback'])->name('tamara.callback');
    Route::post('/tamara/webhook', [ProviderPublicPaymentController::class, 'tamaraWebhook'])->name('tamara.webhook');

    Route::get('/{token}', [ProviderPublicPaymentController::class, 'show'])->name('show');
    Route::get('/{token}/attachment', [ProviderPublicPaymentController::class, 'downloadAttachment'])->name('attachment');
    Route::match(['get', 'post'], '/{token}/pay', [ProviderPublicPaymentController::class, 'selectMethod'])
        ->middleware('throttle:20,5')
        ->name('select');
});

// ─── 2. Service Provider Authentication & OTP Password Recovery ───
Route::prefix('provider')->name('provider.')->group(function () {
    Route::middleware('provider.guest')->group(function () {
        Route::get('/login', [ProviderAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [ProviderAuthController::class, 'login'])->middleware('throttle:10,1')->name('login.submit');

        Route::get('/register', [ProviderAuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [ProviderAuthController::class, 'register'])->middleware('throttle:10,5')->name('register.submit');

        Route::get('/forgot-password', [ProviderAuthController::class, 'showForgotPassword'])->name('password.request');
        Route::post('/forgot-password', [ProviderAuthController::class, 'sendOtp'])->middleware('throttle:5,5')->name('password.otp.send');

        Route::get('/reset-password-otp', [ProviderAuthController::class, 'showVerifyReset'])->name('password.verify.form');
        Route::post('/reset-password-otp/resend', [ProviderAuthController::class, 'resendOtp'])->middleware('throttle:5,5')->name('password.otp.resend');
        Route::post('/reset-password-otp', [ProviderAuthController::class, 'resetPassword'])->middleware('throttle:10,5')->name('password.otp.reset');
    });

    // ─── 3. Authenticated Service Provider Portal ───
    Route::middleware('provider.auth')->group(function () {
        Route::post('/logout', [ProviderAuthController::class, 'logout'])->name('logout');

        Route::get('/dashboard', [ProviderDashboardController::class, 'index'])->name('dashboard');
        Route::get('/wallet', [ProviderDashboardController::class, 'wallet'])->name('wallet');
        Route::get('/profile', [ProviderDashboardController::class, 'profile'])->name('profile');
        Route::put('/profile', [ProviderDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::put('/profile/password', [ProviderDashboardController::class, 'updatePassword'])->name('profile.password');
        Route::get('/profile/documents/{docKey}', [ProviderDashboardController::class, 'downloadDocument'])->name('profile.document');

        // Provider Invoices (creating requires an active monthly subscription!)
        Route::get('/invoices', [ProviderInvoiceController::class, 'index'])->name('invoices.index');
        Route::middleware('provider.subscribed')->group(function () {
            Route::get('/invoices/create', [ProviderInvoiceController::class, 'create'])->name('invoices.create');
            Route::post('/invoices', [ProviderInvoiceController::class, 'store'])->name('invoices.store');
        });
        Route::get('/invoices/{invoice}', [ProviderInvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{invoice}/print', [ProviderInvoiceController::class, 'print'])->name('invoices.print');
        Route::get('/invoices/{invoice}/attachment', [ProviderInvoiceController::class, 'downloadAttachment'])->name('invoices.attachment');
        Route::post('/invoices/{invoice}/check-status', [ProviderInvoiceController::class, 'checkStatus'])->name('invoices.check-status');
    });
});

// ─── 4. Isolated Admin Section for Managing Service Providers ───
Route::prefix('admin/service-providers')
    ->name('admin.providers.')
    ->middleware(['auth:web', 'verified', 'can:manageUsers,App\Models\Contract'])
    ->group(function () {
        Route::get('/', [ServiceProviderAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/list', [ServiceProviderAdminController::class, 'index'])->name('index');
        Route::get('/wallets', [ServiceProviderAdminController::class, 'wallets'])->name('wallets.index');

        Route::get('/invoices', [ServiceProviderAdminController::class, 'invoices'])->name('invoices.index');
        Route::get('/invoices/{invoice}', [ServiceProviderAdminController::class, 'showInvoice'])->name('invoices.show');
        Route::get('/invoices/{invoice}/attachment', [ServiceProviderAdminController::class, 'downloadInvoiceAttachment'])->name('invoices.attachment');
        Route::post('/invoices/{invoice}/check-status', [ServiceProviderAdminController::class, 'checkInvoiceStatus'])->name('invoices.check-status');
        Route::post('/invoices/{invoice}/confirm-paid', [ServiceProviderAdminController::class, 'confirmInvoicePaid'])->name('invoices.confirm-paid');

        Route::get('/mail-settings', [ServiceProviderAdminController::class, 'mailSettings'])->name('mail.index');
        Route::post('/mail-settings', [ServiceProviderAdminController::class, 'saveMailSettings'])->name('mail.save');
        Route::post('/mail-settings/test', [ServiceProviderAdminController::class, 'testMailSettings'])->name('mail.test');

        Route::get('/{provider}', [ServiceProviderAdminController::class, 'show'])->name('show');
        Route::get('/{provider}/documents/{docKey}', [ServiceProviderAdminController::class, 'downloadDocument'])->name('document');
        Route::post('/{provider}/subscription/activate', [ServiceProviderAdminController::class, 'activateSubscription'])->name('subscription.activate');
        Route::post('/{provider}/subscription/suspend', [ServiceProviderAdminController::class, 'suspendSubscription'])->name('subscription.suspend');
        Route::post('/{provider}/toggle-active', [ServiceProviderAdminController::class, 'toggleActive'])->name('toggle-active');
        Route::post('/{provider}/wallet/settle', [ServiceProviderAdminController::class, 'settleWallet'])->name('wallet.settle');
    });
