<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider;
use App\Services\ProviderDocumentService;
use App\Services\ProviderSubscriptionConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var ServiceProvider $provider */
        $provider = Auth::guard('provider')->user();

        $totalInvoices   = $provider->invoices()->count();
        $paidInvoices    = $provider->invoices()->where('status', 'paid')->count();
        $pendingInvoices = $provider->invoices()->where('status', 'pending')->count();
        $recentInvoices  = $provider->invoices()->latest()->take(6)->get();
        $recentTx        = $provider->walletTransactions()->latest()->take(5)->get();
        $subConfig       = ProviderSubscriptionConfigService::getConfig();
        $waSubscribeUrl  = ProviderSubscriptionConfigService::buildSubscribeWhatsAppUrl($provider);

        return view('provider.dashboard', compact(
            'provider',
            'totalInvoices',
            'paidInvoices',
            'pendingInvoices',
            'recentInvoices',
            'recentTx',
            'subConfig',
            'waSubscribeUrl'
        ));
    }

    public function subscription()
    {
        /** @var ServiceProvider $provider */
        $provider       = Auth::guard('provider')->user();
        $subConfig      = ProviderSubscriptionConfigService::getConfig();
        $subscriptions  = $provider->subscriptions()->latest()->take(15)->get();
        $waSubscribeUrl = ProviderSubscriptionConfigService::buildSubscribeWhatsAppUrl($provider);

        return view('provider.subscription', compact(
            'provider',
            'subConfig',
            'subscriptions',
            'waSubscribeUrl'
        ));
    }

    public function wallet()
    {
        /** @var ServiceProvider $provider */
        $provider = Auth::guard('provider')->user();

        $transactions = $provider->walletTransactions()
            ->with('invoice')
            ->latest()
            ->paginate(20);

        return view('provider.wallet', compact('provider', 'transactions'));
    }

    public function profile()
    {
        /** @var ServiceProvider $provider */
        $provider      = Auth::guard('provider')->user();
        $subscriptions = $provider->subscriptions()->latest()->take(10)->get();
        $subConfig     = ProviderSubscriptionConfigService::getConfig();
        $waEditUrl     = ProviderSubscriptionConfigService::buildEditRequestWhatsAppUrl($provider);

        return view('provider.profile', compact('provider', 'subscriptions', 'subConfig', 'waEditUrl'));
    }

    /**
     * Providers are NOT allowed to edit registered business info, service type, bank details, or documents.
     * Any modification must be performed by Administration for KYC compliance.
     */
    public function updateProfile(Request $request)
    {
        abort(403, 'تعديل البيانات الأساسية أو نوع الخدمة أو الوثائق غير متاح من حساب مقدم الخدمة ويتم حصرياً عن طريق الإدارة.');
    }

    public function downloadDocument(Request $request, string $docKey)
    {
        /** @var ServiceProvider $provider */
        $provider = Auth::guard('provider')->user();
        $path = $provider->documentPathForKey($docKey);

        if (!$path) {
            abort(404, 'المستند غير موجود.');
        }

        return ProviderDocumentService::streamResponse($path, $request, "provider_{$provider->id}_{$docKey}");
    }

    public function updatePassword(Request $request)
    {
        /** @var ServiceProvider $provider */
        $provider = Auth::guard('provider')->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'يرجى إدخال كلمة المرور الحالية.',
            'password.confirmed'        => 'تأكيد كلمة المرور غير مطابق.',
            'password.min'              => 'كلمة المرور الجديدة يجب أن لا تقل عن 8 أحرف.',
        ]);

        if (!Hash::check($validated['current_password'], $provider->password)) {
            return back()->withErrors(['current_password' => 'كلمة المرور الحالية غير صحيحة.']);
        }

        $provider->update(['password' => $validated['password']]);

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح.');
    }
}
