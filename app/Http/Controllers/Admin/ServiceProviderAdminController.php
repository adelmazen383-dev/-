<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProviderServiceType;
use App\Http\Controllers\Controller;
use App\Models\ProviderInvoice;
use App\Models\ProviderWalletTransaction;
use App\Models\ServiceProvider;
use App\Services\MailConfigService;
use App\Services\ProviderDocumentService;
use App\Services\ProviderPaymentService;
use App\Services\ProviderSubscriptionConfigService;
use App\Services\ProviderSubscriptionService;
use App\Services\ProviderWalletService;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ServiceProviderAdminController extends Controller
{
    public function __construct(
        private ProviderSubscriptionService $subscriptionService,
        private ProviderWalletService $walletService,
        private ProviderPaymentService $paymentService
    ) {
    }

    /**
     * Isolated overview dashboard for Service Providers (completely separate from core site Dashboard).
     */
    public function dashboard()
    {
        $totalProviders = ServiceProvider::count();
        $activeSubscribers = ServiceProvider::where('is_active', true)
            ->where('subscription_status', 'active')
            ->where('subscription_ends_at', '>', now())
            ->count();
        $pendingSubscribers = ServiceProvider::where(function ($q) {
            $q->where('subscription_status', '!=', 'active')
                ->orWhereNull('subscription_ends_at')
                ->orWhere('subscription_ends_at', '<=', now());
        })->count();

        $totalWalletOwed = (float) ServiceProvider::sum('wallet_balance');
        $totalSettled = (float) ServiceProvider::sum('total_settled');
        $totalInvoices = ProviderInvoice::count();
        $paidInvoices = ProviderInvoice::where('status', 'paid')->count();
        $pendingInvoices = ProviderInvoice::where('status', 'pending')->count();
        $paidVolume = (float) ProviderInvoice::where('status', 'paid')->sum('amount');

        $providersWithBalance = ServiceProvider::where('wallet_balance', '>', 0)
            ->orderByDesc('wallet_balance')
            ->take(8)
            ->get();

        $recentProviders = ServiceProvider::latest()->take(6)->get();
        $recentInvoices = ProviderInvoice::with('provider')->latest()->take(8)->get();

        $byServiceType = [];
        foreach (ProviderServiceType::cases() as $type) {
            $byServiceType[$type->value] = [
                'enum' =>$type,
                'count' =>ServiceProvider::where('service_type', $type->value)->count(),
            ];
        }

        return view('admin.providers.dashboard', compact(
            'totalProviders',
            'activeSubscribers',
            'pendingSubscribers',
            'totalWalletOwed',
            'totalSettled',
            'totalInvoices',
            'paidInvoices',
            'pendingInvoices',
            'paidVolume',
            'providersWithBalance',
            'recentProviders',
            'recentInvoices',
            'byServiceType'
        ));
    }

    /**
     * List & filter all service providers.
     */
    public function index(Request $request)
    {
        $query = ServiceProvider::withCount([
            'invoices',
            'invoices as paid_invoices_count' =>fn ($q) =>$q->where('status', 'paid'),
        ])->latest();

        if ($request->filled('search')) {
            $s = trim((string) $request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('business_name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('custom_service_name', 'like', "%{$s}%");
            });
        }

        if ($request->filled('service_type') && in_array($request->service_type, ProviderServiceType::values(), true)) {
            $query->where('service_type', $request->service_type);
        }

        if ($request->filled('sub_status')) {
            match ($request->sub_status) {
                'active' =>$query->where('is_active', true)
                    ->where('subscription_status', 'active')
                    ->where('subscription_ends_at', '>', now()),
                'expired' =>$query->whereNotNull('subscription_ends_at')
                    ->where('subscription_ends_at', '<=', now()),
                'inactive' =>$query->where('subscription_status', 'inactive'),
                'suspended' =>$query->where(function ($q) {
                    $q->where('is_active', false)->orWhere('subscription_status', 'suspended');
                }),
                default =>null,
            };
        }

        $providers = $query->paginate(15)->withQueryString();
        $serviceTypes = ProviderServiceType::options();

        return view('admin.providers.index', compact('providers', 'serviceTypes'));
    }

    /**
     * Full provider dossier: service details, subscription management, wallet settlement, invoices.
     */
    public function show(ServiceProvider $provider)
    {
        $subscriptions = $provider->subscriptions()->with('activatedByAdmin')->latest()->take(15)->get();
        $transactions = $provider->walletTransactions()->with(['invoice', 'admin'])->latest()->take(20)->get();
        $invoices = $provider->invoices()->latest()->paginate(10);
        $contracts = $provider->isServicedSuites() ? $provider->contracts()->latest()->take(15)->get() : collect();
        $subConfig = ProviderSubscriptionConfigService::getConfig();
        $serviceTypes = ProviderServiceType::options();

        return view('admin.providers.show', compact('provider', 'subscriptions', 'transactions', 'invoices', 'contracts', 'subConfig', 'serviceTypes'));
    }

    /**
     * Admin-only update of a provider's locked details (profile, service type, bank details).
     */
    public function updateProvider(Request $request, ServiceProvider $provider)
    {
        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:150'],
            'business_name'       => ['required', 'string', 'max:150'],
            'service_type'        => ['required', 'string', Rule::in(ProviderServiceType::values())],
            'property_kind'       => ['nullable', 'string', 'max:60'],
            'custom_service_name' => ['nullable', 'required_if:service_type,other', 'string', 'max:150'],
            'service_description' => ['required', 'string', 'min:5', 'max:1000'],
            'city'                => ['nullable', 'string', 'max:100'],
            'commercial_register' => ['nullable', 'string', 'max:100'],
            'national_id_number'  => ['nullable', 'string', 'max:30'],
            'bank_name'           => ['nullable', 'string', 'max:120'],
            'bank_account_name'   => ['nullable', 'string', 'max:150'],
            'iban'                => ['nullable', 'string', 'max:50'],
        ]);

        $provider->update([
            'name'                => trim($validated['name']),
            'business_name'       => trim($validated['business_name']),
            'service_type'        => $validated['service_type'],
            'property_kind'       => $validated['service_type'] === 'serviced_suites' ? ($validated['property_kind'] ?? null) : null,
            'custom_service_name' => $validated['service_type'] === 'other' ? trim((string) ($validated['custom_service_name'] ?? '')) : null,
            'service_description' => trim($validated['service_description']),
            'city'                => $validated['city'] ?? null,
            'commercial_register' => $validated['commercial_register'] ?? null,
            'national_id_number'  => $validated['national_id_number'] ?? null,
            'bank_name'           => $validated['bank_name'] ?? null,
            'bank_account_name'   => $validated['bank_account_name'] ?? null,
            'iban'                => !empty($validated['iban']) ? strtoupper(preg_replace('/\s+/', '', $validated['iban'])) : null,
        ]);

        return back()->with('success', 'تم تحديث بيانات مقدم الخدمة بنجاح من قبل الإدارة.');
    }

    /**
     * Subscription Plans, Pricing & Contact Channels Control Panel.
     */
    public function subscriptionSettings()
    {
        $config = ProviderSubscriptionConfigService::getConfig();
        return view('admin.providers.subscription-settings', compact('config'));
    }

    public function saveSubscriptionSettings(Request $request)
    {
        $validated = $request->validate([
            'plan_1m_enabled'  => ['nullable', 'boolean'],
            'plan_1m_price'    => ['required', 'numeric', 'min:0', 'max:100000'],
            'plan_1m_label'    => ['required', 'string', 'max:120'],
            'plan_3m_enabled'  => ['nullable', 'boolean'],
            'plan_3m_price'    => ['required', 'numeric', 'min:0', 'max:100000'],
            'plan_3m_label'    => ['required', 'string', 'max:120'],
            'plan_6m_enabled'  => ['nullable', 'boolean'],
            'plan_6m_price'    => ['required', 'numeric', 'min:0', 'max:100000'],
            'plan_6m_label'    => ['required', 'string', 'max:120'],
            'plan_12m_enabled' => ['nullable', 'boolean'],
            'plan_12m_price'   => ['required', 'numeric', 'min:0', 'max:100000'],
            'plan_12m_label'   => ['required', 'string', 'max:120'],
            'features_text'    => ['nullable', 'string', 'max:2000'],
            'whatsapp'         => ['nullable', 'string', 'max:30'],
            'phone'            => ['nullable', 'string', 'max:30'],
            'email'            => ['nullable', 'email', 'max:180'],
            'bank_name'        => ['nullable', 'string', 'max:120'],
            'bank_account'     => ['nullable', 'string', 'max:150'],
            'bank_iban'        => ['nullable', 'string', 'max:50'],
            'contact_note'     => ['nullable', 'string', 'max:1000'],
        ]);

        ProviderSubscriptionConfigService::saveConfig([
            'plan_1m_enabled'  => $request->boolean('plan_1m_enabled'),
            'plan_1m_price'    => $validated['plan_1m_price'],
            'plan_1m_label'    => $validated['plan_1m_label'],
            'plan_3m_enabled'  => $request->boolean('plan_3m_enabled'),
            'plan_3m_price'    => $validated['plan_3m_price'],
            'plan_3m_label'    => $validated['plan_3m_label'],
            'plan_6m_enabled'  => $request->boolean('plan_6m_enabled'),
            'plan_6m_price'    => $validated['plan_6m_price'],
            'plan_6m_label'    => $validated['plan_6m_label'],
            'plan_12m_enabled' => $request->boolean('plan_12m_enabled'),
            'plan_12m_price'   => $validated['plan_12m_price'],
            'plan_12m_label'   => $validated['plan_12m_label'],
            'features_text'    => $validated['features_text'] ?? '',
            'whatsapp'         => $validated['whatsapp'] ?? '',
            'phone'            => $validated['phone'] ?? '',
            'email'            => $validated['email'] ?? '',
            'bank_name'        => $validated['bank_name'] ?? '',
            'bank_account'     => $validated['bank_account'] ?? '',
            'bank_iban'        => $validated['bank_iban'] ?? '',
            'contact_note'     => $validated['contact_note'] ?? '',
        ]);

        return back()->with('success', 'تم حفظ إعدادات باقات الاشتراك والأسعار وبيانات التواصل بنجاح وستظهر لمقدمي الخدمات فوراً.');
    }

    /**
     * Securely stream a provider's private KYC document for Admin verification.
     */
    public function downloadDocument(Request $request, ServiceProvider $provider, string $docKey)
    {
        $path = $provider->documentPathForKey($docKey);

        if (!$path) {
            abort(404, 'المستند غير موجود.');
        }

        return ProviderDocumentService::streamResponse($path, $request, "provider_{$provider->id}_{$docKey}");
    }

    /**
     * Activate or extend monthly subscription for a provider.
     */
    public function activateSubscription(Request $request, ServiceProvider $provider)
    {
        $validated = $request->validate([
            'duration_months' =>['required', 'integer', 'min:1', 'max:24'],
            'amount' =>['nullable', 'numeric', 'min:0', 'max:100000'],
            'payment_reference' =>['nullable', 'string', 'max:120'],
            'notes' =>['nullable', 'string', 'max:500'],
        ]);

        $sub = $this->subscriptionService->activate(
            $provider,
            (int) Auth::id(),
            (int) $validated['duration_months'],
            (float) ($validated['amount'] ?? 0),
            $validated['payment_reference'] ?? null,
            $validated['notes'] ?? null
        );

        return back()->with('success', 'تم تفعيل اشتراك ' . $provider->business_name . ' حتى تاريخ ' . $sub->ends_at->format('Y-m-d') . '، وأصبح بإمكانه إنشاء الفواتير فوراً.');
    }

    /**
     * Suspend active subscription (blocks creating new invoices immediately).
     */
    public function suspendSubscription(Request $request, ServiceProvider $provider)
    {
        $this->subscriptionService->suspend($provider, (int) Auth::id(), $request->input('notes'));

        return back()->with('success', 'تم إيقاف اشتراك ' . $provider->business_name . ' ولن يتمكن من إنشاء فواتير جديدة حتى إعادة التفعيل.');
    }

    /**
     * Toggle provider login account active/suspended.
     */
    public function toggleActive(ServiceProvider $provider)
    {
        $provider->update(['is_active' =>!$provider->is_active]);

        return back()->with('success', $provider->is_active
            ? 'تم تفعيل حساب ' . $provider->business_name . '.'
            : 'تم إيقاف دخول حساب ' . $provider->business_name . '.');
    }

    /**
     * Transfer payout to provider and zero (or partially settle) their wallet balance.
     */
    public function settleWallet(Request $request, ServiceProvider $provider)
    {
        $validated = $request->validate([
            'settle_mode' =>['nullable', 'string', 'in:full,partial'],
            'amount' =>['nullable', 'numeric', 'min:0.01', 'max:500000'],
            'reference' =>['nullable', 'string', 'max:120'],
            'notes' =>['nullable', 'string', 'max:500'],
        ]);

        $customAmount = ($validated['settle_mode'] ?? 'full') === 'partial' && !empty($validated['amount'])
            ? (float) $validated['amount']
            : null;

        $res = $this->walletService->settleWallet(
            $provider,
            (int) Auth::id(),
            $customAmount,
            $validated['reference'] ?? null,
            $validated['notes'] ?? null
        );

        if (!$res['success']) {
            return back()->with('error', $res['error']);
        }

        return back()->with('success', 'تم تسجيل تحويل مبلغ ' . number_format($res['amount'], 2) . ' ر.س إلى ' . $provider->business_name . '. الرصيد المتبقي في المحفظة: ' . number_format($res['balance_after'], 2) . ' ر.س.');
    }

    /**
     * Admin view of ALL service provider invoices (completely separate from core /invoices).
     */
    public function invoices(Request $request)
    {
        $query = ProviderInvoice::with('provider')->latest();

        if ($request->filled('search')) {
            $s = trim((string) $request->search);
            $query->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                    ->orWhere('customer_name', 'like', "%{$s}%")
                    ->orWhere('customer_phone', 'like', "%{$s}%")
                    ->orWhere('title', 'like', "%{$s}%")
                    ->orWhereHas('provider', fn ($pq) =>$pq->where('business_name', 'like', "%{$s}%")->orWhere('name', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('service_type') && in_array($request->service_type, ProviderServiceType::values(), true)) {
            $query->where('service_type', $request->service_type);
        }

        if ($request->filled('status') && in_array($request->status, ['pending', 'paid', 'failed', 'cancelled'], true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('provider_id')) {
            $query->where('service_provider_id', (int) $request->provider_id);
        }

        $invoices = $query->paginate(20)->withQueryString();
        $serviceTypes = ProviderServiceType::options();
        $providers = ServiceProvider::orderBy('business_name')->get(['id', 'business_name', 'name']);

        return view('admin.providers.invoices', compact('invoices', 'serviceTypes', 'providers'));
    }

    /**
     * Admin detailed view of a single provider invoice.
     */
    public function showInvoice(ProviderInvoice $invoice)
    {
        $invoice->load(['provider', 'verifiedByAdmin', 'walletTransaction']);
        return view('admin.providers.invoice-show', compact('invoice'));
    }

    public function downloadInvoiceAttachment(Request $request, ProviderInvoice $invoice)
    {
        if (!$invoice->quote_attachment_path) {
            abort(404);
        }

        return ProviderDocumentService::streamResponse($invoice->quote_attachment_path, $request, "invoice_{$invoice->invoice_number}_attachment");
    }

    /**
     * Admin triggers live gateway status verification (Telr / Tamara) for a provider invoice.
     */
    public function checkInvoiceStatus(ProviderInvoice $invoice)
    {
        if ($invoice->isPaid()) {
            return back()->with('success', 'الفاتورة مدفوعة ومضافة إلى محفظة مقدم الخدمة بالفعل.');
        }

        if ($invoice->payment_method === 'tamara' && $invoice->tamara_order_id) {
            $res = $this->paymentService->verifyTamaraOrder($invoice);
            if (!empty($res['paid'])) {
                $this->paymentService->markPaidAndCreditWallet($invoice, 'tamara', (int) Auth::id(), [
                    'tamara_status' =>$res['status'] ?? 'authorised',
                ]);
                return back()->with('success', 'تم التحقق من الدفع لدى تمارا وتمت إضافة الرصيد إلى محفظة مقدم الخدمة.');
            }
            return back()->with('error', 'لم يتم تأكيد الدفع لدى تمارا بعد. الحالة: ' . ($res['status'] ?? 'غير معروف'));
        }

        if ($invoice->telr_order_ref) {
            $res = $this->paymentService->checkTelrOrder($invoice->telr_order_ref);
            if ($res['success'] && ($res['code'] ?? 0) === 3) {
                $this->paymentService->markPaidAndCreditWallet($invoice, null, (int) Auth::id());
                return back()->with('success', 'تم التحقق من الدفع عبر البوابة وتمت إضافة الرصيد إلى محفظة مقدم الخدمة.');
            }
            return back()->with('error', 'العملية غير مدفوعة بعد لدى البوابة (' . ($res['message'] ?? 'بانتظار الدفع') . ').');
        }

        return back()->with('error', 'لا توجد جلسة دفع مسجلة لهذه الفاتورة بعد.');
    }

    /**
     * Admin manually confirms a verified payment and credits the provider's wallet atomically.
     */
    public function confirmInvoicePaid(Request $request, ProviderInvoice $invoice)
    {
        if ($invoice->isPaid() && $invoice->isWalletCredited()) {
            return back()->with('success', 'الفاتورة مؤكدة ومضافة إلى المحفظة مسبقاً.');
        }

        $method = $request->input('payment_method', $invoice->payment_method ?: 'standard');
        $this->paymentService->markPaidAndCreditWallet($invoice, $method, (int) Auth::id());

        return back()->with('success', 'تم تأكيد سداد الفاتورة رقم ' . $invoice->invoice_number . ' وإضافة مبلغ ' . number_format((float) $invoice->amount, 2) . ' ر.س إلى محفظة ' . $invoice->provider->business_name . '.');
    }

    /**
     * Wallets & End-of-Day Settlements center.
     */
    public function wallets()
    {
        $providersWithBalance = ServiceProvider::where('wallet_balance', '>', 0)
            ->orderByDesc('wallet_balance')
            ->get();

        $totalOwed = (float) ServiceProvider::sum('wallet_balance');
        $totalSettled = (float) ServiceProvider::sum('total_settled');
        $totalEarned = (float) ServiceProvider::sum('total_earned');

        $transactions = ProviderWalletTransaction::with(['provider', 'invoice', 'admin'])
            ->latest()
            ->paginate(25);

        return view('admin.providers.wallets', compact(
            'providersWithBalance',
            'totalOwed',
            'totalSettled',
            'totalEarned',
            'transactions'
        ));
    }

    /**
     * SMTP & OTP Mail Settings for Service Providers password recovery.
     */
    public function mailSettings()
    {
        $mail = MailConfigService::getSettingsForView();
        return view('admin.providers.mail-settings', compact('mail'));
    }

    public function saveMailSettings(Request $request)
    {
        $validated = $request->validate([
            'smtp_enabled' =>['nullable', 'boolean'],
            'smtp_host' =>['nullable', 'required_if:smtp_enabled,1', 'string', 'max:200'],
            'smtp_port' =>['nullable', 'required_if:smtp_enabled,1', 'integer', 'min:1', 'max:65535'],
            'smtp_encryption' =>['nullable', 'string', 'in:tls,ssl,none'],
            'smtp_username' =>['nullable', 'string', 'max:200'],
            'smtp_password' =>['nullable', 'string', 'max:300'],
            'smtp_from_address' =>['nullable', 'required_if:smtp_enabled,1', 'email', 'max:200'],
            'smtp_from_name' =>['nullable', 'string', 'max:150'],
        ], [
            'smtp_host.required_if' =>'يرجى إدخال عنوان خادم SMTP عند التفعيل.',
            'smtp_port.required_if' =>'يرجى إدخال المنفذ (Port) عند التفعيل.',
            'smtp_from_address.required_if' =>'يرجى إدخال بريد المرسل عند التفعيل.',
        ]);

        MailConfigService::saveSettings([
            'smtp_enabled' =>$request->boolean('smtp_enabled'),
            'smtp_host' =>$validated['smtp_host'] ?? '',
            'smtp_port' =>$validated['smtp_port'] ?? 587,
            'smtp_encryption' =>$validated['smtp_encryption'] ?? 'tls',
            'smtp_username' =>$validated['smtp_username'] ?? '',
            'smtp_password' =>$validated['smtp_password'] ?? '',
            'smtp_from_address' =>$validated['smtp_from_address'] ?? '',
            'smtp_from_name' =>$validated['smtp_from_name'] ?? 'بوابة مقدمي الخدمات',
        ]);

        return back()->with('success', 'تم حفظ إعدادات البريد الإلكتروني (SMTP) لرموز OTP بنجاح.');
    }

    public function testMailSettings(Request $request)
    {
        $validated = $request->validate([
            'test_email' =>['required', 'email', 'max:200'],
        ], [
            'test_email.required' =>'يرجى إدخال البريد الإلكتروني لإرسال رسالة الاختبار إليه.',
        ]);

        try {
            MailConfigService::apply();
            Mail::raw(
                "رسالة اختبار من بوابة مقدمي الخدمات — إعدادات البريد (SMTP) لرموز التحقق OTP تعمل بنجاح! (" . now()->format('Y-m-d H:i') . ")",
                function ($msg) use ($validated) {
                    $msg->to($validated['test_email'])
                        ->subject('اختبار إرسال البريد ورموز OTP — بوابة مقدمي الخدمات');
                }
            );
            return back()->with('success', 'تم إرسال رسالة الاختبار بنجاح إلى: ' . $validated['test_email']);
        } catch (\Throwable $e) {
            return back()->with('error', 'فشل إرسال رسالة الاختبار: ' . $e->getMessage());
        }
    }
}
