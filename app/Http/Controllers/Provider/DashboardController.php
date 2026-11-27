<?php

namespace App\Http\Controllers\Provider;

use App\Enums\ProviderServiceType;
use App\Http\Controllers\Controller;
use App\Models\ServiceProvider;
use App\Services\ProviderDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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

        return view('provider.dashboard', compact(
            'provider',
            'totalInvoices',
            'paidInvoices',
            'pendingInvoices',
            'recentInvoices',
            'recentTx'
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
        $provider     = Auth::guard('provider')->user();
        $serviceTypes = ProviderServiceType::options();
        $subscriptions = $provider->subscriptions()->latest()->take(10)->get();

        return view('provider.profile', compact('provider', 'serviceTypes', 'subscriptions'));
    }

    public function updateProfile(Request $request)
    {
        /** @var ServiceProvider $provider */
        $provider = Auth::guard('provider')->user();

        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:150'],
            'business_name'       => ['required', 'string', 'max:150'],
            'service_type'        => ['required', 'string', Rule::in(ProviderServiceType::values())],
            'custom_service_name' => ['nullable', 'required_if:service_type,other', 'string', 'max:150'],
            'service_description' => ['required', 'string', 'min:10', 'max:1000'],
            'city'                => ['nullable', 'string', 'max:100'],
            'commercial_register' => ['nullable', 'string', 'max:100'],
            'bank_name'           => ['nullable', 'string', 'max:120'],
            'bank_account_name'   => ['nullable', 'string', 'max:150'],
            'iban'                => ['nullable', 'string', 'max:50'],
            'property_kind'       => ['nullable', 'string', 'max:60'],
            'national_id_number'  => ['nullable', 'string', 'max:30'],
            'national_id_front'   => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'national_id_back'    => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'tourism_license'     => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'commercial_doc'      => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'photography_license' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], [
            'custom_service_name.required_if' => 'يرجى كتابة اسم الخدمة عند اختيار (أخرى).',
        ]);

        $updates = [
            'name'                => trim($validated['name']),
            'business_name'       => trim($validated['business_name']),
            'service_type'        => $validated['service_type'],
            'property_kind'       => $validated['service_type'] === 'serviced_suites' ? ($validated['property_kind'] ?? $provider->property_kind) : null,
            'custom_service_name' => $validated['service_type'] === 'other'
                ? trim((string) $validated['custom_service_name'])
                : null,
            'service_description' => trim($validated['service_description']),
            'city'                => $validated['city'] ?? null,
            'commercial_register' => $validated['commercial_register'] ?? null,
            'national_id_number'  => $validated['national_id_number'] ?? $provider->national_id_number,
            'bank_name'           => $validated['bank_name'] ?? null,
            'bank_account_name'   => $validated['bank_account_name'] ?? null,
            'iban'                => !empty($validated['iban']) ? strtoupper(preg_replace('/\s+/', '', $validated['iban'])) : null,
        ];

        $docDir  = "provider-documents/{$provider->id}";
        $fileMap = [
            'national_id_front'   => 'national_id_front_path',
            'national_id_back'    => 'national_id_back_path',
            'tourism_license'     => 'tourism_license_path',
            'commercial_doc'      => 'commercial_doc_path',
            'photography_license' => 'photography_license_path',
        ];
        foreach ($fileMap as $inputName => $col) {
            if ($request->hasFile($inputName)) {
                if (!empty($provider->{$col}) && Storage::disk('local')->exists($provider->{$col})) {
                    Storage::disk('local')->delete($provider->{$col});
                }
                $updates[$col] = ProviderDocumentService::store($request->file($inputName), $docDir, $inputName);
            }
        }

        $provider->update($updates);

        return back()->with('success', '✅ تم تحديث بياناتك ووثائقك وبيانات التحويل البنكي بنجاح.');
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

        return back()->with('success', '✅ تم تغيير كلمة المرور بنجاح.');
    }
}
