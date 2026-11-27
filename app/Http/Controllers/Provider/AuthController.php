<?php

namespace App\Http\Controllers\Provider;

use App\Enums\ProviderServiceType;
use App\Http\Controllers\Controller;
use App\Models\ServiceProvider;
use App\Services\ProviderDocumentService;
use App\Services\ProviderOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function __construct(private ProviderOtpService $otpService)
    {
    }

    /* ── Login ── */

    public function showLogin()
    {
        return view('provider.auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'login'    => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ], [
            'login.required'    => 'يرجى إدخال البريد الإلكتروني أو رقم الجوال.',
            'password.required' => 'يرجى إدخال كلمة المرور.',
        ]);

        $throttleKey = 'provider-login:' . Str::lower(trim($validated['login'])) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()
                ->withInput($request->only('login', 'remember'))
                ->withErrors(['login' => "تم تجاوز عدد محاولات الدخول المسموح بها. يرجى المحاولة بعد {$seconds} ثانية."]);
        }

        $provider = $this->otpService->findProviderByIdentifier($validated['login']);

        if (!$provider || !Hash::check($validated['password'], $provider->password)) {
            RateLimiter::hit($throttleKey, 60);
            return back()
                ->withInput($request->only('login', 'remember'))
                ->withErrors(['login' => 'بيانات تسجيل الدخول غير صحيحة. تأكد من البريد/الجوال وكلمة المرور.']);
        }

        if (!$provider->is_active) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'تم إيقاف هذا الحساب مؤقتاً. يُرجى التواصل مع الإدارة.']);
        }

        RateLimiter::clear($throttleKey);

        Auth::guard('provider')->login($provider, $request->boolean('remember'));
        $request->session()->regenerate();
        $provider->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('provider.dashboard'))
            ->with('success', 'أهلاً بك يا ' . $provider->name . '!');
    }

    /* ── Register ── */

    public function showRegister()
    {
        $serviceTypes = ProviderServiceType::options();
        return view('provider.auth.register', compact('serviceTypes'));
    }

    public function register(Request $request)
    {
        $rawPhone = (string) $request->input('phone', '');
        $normalizedPhone = ServiceProvider::normalizePhone($rawPhone);
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email', ''))),
            'phone' => $normalizedPhone,
        ]);

        $serviceType = (string) $request->input('service_type', '');

        $rules = [
            'name'                => ['required', 'string', 'max:150'],
            'business_name'       => ['required', 'string', 'max:150'],
            'email'               => ['required', 'email', 'max:180', 'unique:service_providers,email'],
            'phone'               => ['required', 'string', 'min:9', 'max:20', 'unique:service_providers,phone'],
            'service_type'        => ['required', 'string', Rule::in(ProviderServiceType::values())],
            'property_kind'       => ['nullable', 'string', 'max:60'],
            'custom_service_name' => ['nullable', 'required_if:service_type,other', 'string', 'max:150'],
            'service_description' => ['required', 'string', 'min:10', 'max:1000'],
            'city'                => ['nullable', 'string', 'max:100'],
            'commercial_register' => ['nullable', 'string', 'max:100'],
            'national_id_number'  => ['nullable', 'string', 'max:30'],
            'bank_name'           => ['required', 'string', 'max:120'],
            'bank_account_name'   => ['required', 'string', 'max:150'],
            'iban'                => ['required', 'string', 'min:15', 'max:50'],
            'bank_owner_confirmed'=> $serviceType === 'serviced_suites' ? ['accepted'] : ['nullable'],
            'password'            => ['required', 'confirmed', Password::min(8)],

            // KYC Document Uploads (images or PDF, max 5MB each)
            'national_id_front'   => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'national_id_back'    => $serviceType === 'serviced_suites'
                ? ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120']
                : ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'tourism_license'     => $serviceType === 'serviced_suites'
                ? ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120']
                : ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'commercial_doc'      => in_array($serviceType, ['coordination', 'photography'], true)
                ? ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120']
                : ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'photography_license' => $serviceType === 'photography'
                ? ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120']
                : ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ];

        $validated = $request->validate($rules, [
            'name.required'                   => 'يرجى إدخال اسم الشخص أو المسؤول.',
            'business_name.required'          => 'يرجى إدخال اسم المنشأة أو اسم الشخص/العلامة التجارية.',
            'email.required'                  => 'يرجى إدخال البريد الإلكتروني.',
            'email.email'                     => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique'                    => 'هذا البريد الإلكتروني مسجل مسبقاً.',
            'phone.required'                  => 'يرجى إدخال رقم الجوال.',
            'phone.unique'                    => 'رقم الجوال هذا مسجل مسبقاً.',
            'service_type.required'           => 'يرجى اختيار نوع الخدمة التي تقدمها.',
            'custom_service_name.required_if' => 'يرجى كتابة اسم الخدمة عند اختيار (أخرى).',
            'service_description.required'    => 'يرجى كتابة وصف واضح للخدمة التي تقدمها.',
            'service_description.min'         => 'يرجى كتابة وصف لا يقل عن 10 أحرف عن خدمتك.',
            'bank_name.required'              => 'يرجى إدخال اسم البنك.',
            'bank_account_name.required'      => 'يرجى إدخال اسم صاحب الحساب البنكي.',
            'iban.required'                   => 'يرجى إدخال رقم الآيبان (IBAN).',
            'iban.min'                        => 'يرجى إدخال رقم آيبان صحيح.',
            'bank_owner_confirmed.accepted'   => 'يجب الإقرار بأن الحساب البنكي بنفس اسم صاحب النشاط.',
            'national_id_front.required'      => 'يرجى إرفاق صورة الهوية الوطنية (الوجه الأمامي).',
            'national_id_back.required'       => 'يرجى إرفاق صورة الهوية الوطنية (الوجه الخلفي) لأصحاب العقارات والأجنحة الفندقية.',
            'tourism_license.required'        => 'يرجى إرفاق رخصة السياحة للأجنحة الفندقية / العقار.',
            'commercial_doc.required'         => 'يرجى إرفاق وثيقة العمل الحر أو السجل التجاري.',
            'photography_license.required'    => 'يرجى إرفاق ترخيص التصوير للمصورين.',
            '*.mimes'                         => 'صيغة الملف يجب أن تكون صورة (JPG / PNG / WEBP) أو PDF.',
            '*.max'                           => 'حجم الملف الواحد يجب أن لا يتجاوز 5 ميجابايت.',
            'password.required'               => 'يرجى إدخال كلمة المرور.',
            'password.confirmed'              => 'تأكيد كلمة المرور غير مطابق.',
            'password.min'                    => 'كلمة المرور يجب أن لا تقل عن 8 أحرف.',
        ]);

        $provider = ServiceProvider::create([
            'name'                 => trim($validated['name']),
            'business_name'        => trim($validated['business_name']),
            'email'                => $validated['email'],
            'phone'                => $validated['phone'],
            'password'             => $validated['password'],
            'service_type'         => $validated['service_type'],
            'property_kind'        => $validated['service_type'] === 'serviced_suites'
                ? ($validated['property_kind'] ?? null)
                : null,
            'custom_service_name'  => $validated['service_type'] === 'other'
                ? trim((string) $validated['custom_service_name'])
                : null,
            'service_description'  => trim($validated['service_description']),
            'city'                 => $validated['city'] ?? null,
            'commercial_register'  => $validated['commercial_register'] ?? null,
            'national_id_number'   => $validated['national_id_number'] ?? null,
            'bank_name'            => trim($validated['bank_name']),
            'bank_account_name'    => trim($validated['bank_account_name']),
            'iban'                 => strtoupper(preg_replace('/\s+/', '', $validated['iban'])),
            'bank_owner_confirmed' => $request->boolean('bank_owner_confirmed'),
            'is_active'            => true,
            'subscription_status'  => 'inactive',
            'wallet_balance'       => 0.00,
            'total_earned'         => 0.00,
            'total_settled'        => 0.00,
            'last_login_at'        => now(),
        ]);

        // Store uploaded KYC documents securely on the private local disk
        $docDir = "provider-documents/{$provider->id}";
        $docPaths = [];
        $fileMap = [
            'national_id_front'   => 'national_id_front_path',
            'national_id_back'    => 'national_id_back_path',
            'tourism_license'     => 'tourism_license_path',
            'commercial_doc'      => 'commercial_doc_path',
            'photography_license' => 'photography_license_path',
        ];
        foreach ($fileMap as $inputName => $columnName) {
            if ($request->hasFile($inputName)) {
                $docPaths[$columnName] = ProviderDocumentService::store($request->file($inputName), $docDir, $inputName);
            }
        }
        if (!empty($docPaths)) {
            $provider->update($docPaths);
        }

        Auth::guard('provider')->login($provider);
        $request->session()->regenerate();

        return redirect()->route('provider.dashboard')
            ->with('success', 'تم تسجيل حسابك ورفع وثائقك بنجاح! ستراجع الإدارة بياناتك وبمجرد تفعيل الاشتراك الشهري سيتاح لك إنشاء الفواتير فوراً.');
    }

    /* ── Logout ── */

    public function logout(Request $request)
    {
        Auth::guard('provider')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('provider.login')
            ->with('success', 'تم تسجيل الخروج بنجاح.');
    }

    /* ── Forgot Password + OTP Flow ── */

    public function showForgotPassword()
    {
        return view('provider.auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
        ], [
            'identifier.required' => 'يرجى إدخال البريد الإلكتروني أو رقم الجوال المسجل.',
        ]);

        $provider = $this->otpService->findProviderByIdentifier($validated['identifier']);

        if (!$provider) {
            return back()
                ->withInput()
                ->withErrors(['identifier' => 'لم نتمكن من العثور على حساب مقدم خدمة مطابق لهذا البريد الإلكتروني أو رقم الجوال.']);
        }

        if (!$provider->is_active) {
            return back()
                ->withInput()
                ->withErrors(['identifier' => 'هذا الحساب موقوف حالياً. يُرجى التواصل مع الإدارة.']);
        }

        $result = $this->otpService->sendResetOtp($provider, $request->ip());

        if (!$result['success']) {
            if (!empty($result['cooldown'])) {
                $request->session()->put('provider_otp_reset', [
                    'provider_id'  => $provider->id,
                    'masked_email' => $provider->maskedEmail(),
                    'sent_at'      => now()->timestamp - (ProviderOtpService::RESEND_COOLDOWN_SECONDS - $result['cooldown']),
                ]);
                return redirect()->route('provider.password.verify.form')
                    ->with('warning', $result['error']);
            }
            return back()->withInput()->with('error', $result['error']);
        }

        $request->session()->put('provider_otp_reset', [
            'provider_id'  => $provider->id,
            'masked_email' => $result['masked_email'],
            'sent_at'      => now()->timestamp,
        ]);

        return redirect()->route('provider.password.verify.form')
            ->with('success', 'تم إرسال رمز التحقق (OTP) المكوّن من 6 أرقام إلى بريدك الإلكتروني: ' . $result['masked_email']);
    }

    public function showVerifyReset(Request $request)
    {
        $ctx = $request->session()->get('provider_otp_reset');
        if (!$ctx || empty($ctx['provider_id'])) {
            return redirect()->route('provider.password.request')
                ->with('error', 'يرجى إدخال بريدك الإلكتروني أو رقم جوالك أولاً لطلب رمز التحقق.');
        }

        $elapsed  = max(0, now()->timestamp - (int) ($ctx['sent_at'] ?? 0));
        $cooldown = max(0, ProviderOtpService::RESEND_COOLDOWN_SECONDS - $elapsed);

        return view('provider.auth.reset-password-otp', [
            'maskedEmail' => $ctx['masked_email'] ?? '***',
            'cooldown'    => $cooldown,
        ]);
    }

    public function resendOtp(Request $request)
    {
        $ctx = $request->session()->get('provider_otp_reset');
        if (!$ctx || empty($ctx['provider_id'])) {
            return redirect()->route('provider.password.request');
        }

        $provider = ServiceProvider::find($ctx['provider_id']);
        if (!$provider) {
            $request->session()->forget('provider_otp_reset');
            return redirect()->route('provider.password.request');
        }

        $result = $this->otpService->sendResetOtp($provider, $request->ip());
        if (!$result['success']) {
            return back()->with('error', $result['error']);
        }

        $request->session()->put('provider_otp_reset', [
            'provider_id'  => $provider->id,
            'masked_email' => $result['masked_email'],
            'sent_at'      => now()->timestamp,
        ]);

        return back()->with('success', 'تم إرسال رمز تحقق جديد إلى بريدك الإلكتروني: ' . $result['masked_email']);
    }

    public function resetPassword(Request $request)
    {
        $ctx = $request->session()->get('provider_otp_reset');
        if (!$ctx || empty($ctx['provider_id'])) {
            return redirect()->route('provider.password.request')
                ->with('error', 'انتهت جلسة الاستعادة. يُرجى طلب رمز تحقق جديد.');
        }

        $validated = $request->validate([
            'otp_code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'otp_code.required'  => 'يرجى إدخال رمز التحقق المكوّن من 6 أرقام.',
            'otp_code.size'      => 'رمز التحقق يجب أن يتكون من 6 أرقام.',
            'password.required'  => 'يرجى إدخال كلمة المرور الجديدة.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',
            'password.min'       => 'كلمة المرور يجب أن لا تقل عن 8 أحرف.',
        ]);

        $provider = ServiceProvider::find($ctx['provider_id']);
        if (!$provider) {
            $request->session()->forget('provider_otp_reset');
            return redirect()->route('provider.password.request')
                ->with('error', 'الحساب غير موجود.');
        }

        $result = $this->otpService->verifyAndResetPassword(
            $provider,
            $validated['otp_code'],
            $validated['password']
        );

        if (!$result['success']) {
            return back()->withInput($request->only('otp_code'))->withErrors(['otp_code' => $result['error']]);
        }

        $request->session()->forget('provider_otp_reset');

        return redirect()->route('provider.login')
            ->with('success', '✅ تم تحديث كلمة المرور بنجاح! يمكنك الآن تسجيل الدخول بكلمة المرور الجديدة.');
    }
}
