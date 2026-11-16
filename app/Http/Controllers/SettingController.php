<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Show the payment fees settings form.
     */
    public function index()
    {
        $visaFee     = (float) Setting::get('fee_visa_percent', 5.00);
        $applePayFee = (float) Setting::get('fee_apple_pay_percent', 5.00);
        $tabbyFee    = (float) Setting::get('fee_tabby_percent', 15.00);
        $tamaraFee   = (float) Setting::get('fee_tamara_percent', 15.00);

        return view('settings.payment', compact('visaFee', 'applePayFee', 'tabbyFee', 'tamaraFee'));
    }

    /**
     * Update payment fees settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'fee_visa_percent'      => ['required', 'numeric', 'min:0', 'max:100'],
            'fee_apple_pay_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'fee_tabby_percent'     => ['required', 'numeric', 'min:0', 'max:100'],
            'fee_tamara_percent'    => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'fee_visa_percent.required'      => 'يرجى إدخال نسبة رسوم البطاقات (فيزا / ماستركارد / مدى).',
            'fee_apple_pay_percent.required' => 'يرجى إدخال نسبة رسوم Apple Pay.',
            'fee_tabby_percent.required'     => 'يرجى إدخال نسبة رسوم تابي.',
            'fee_tamara_percent.required'    => 'يرجى إدخال نسبة رسوم تمارا.',
            'numeric'                        => 'يجب أن تكون النسبة رقماً صحيحاً أو عشرياً.',
            'min'                            => 'النسبة لا يمكن أن تكون سالبة.',
            'max'                            => 'النسبة لا يمكن أن تتجاوز 100%.',
        ]);

        Setting::set('fee_visa_percent', number_format((float) $validated['fee_visa_percent'], 2, '.', ''));
        Setting::set('fee_apple_pay_percent', number_format((float) $validated['fee_apple_pay_percent'], 2, '.', ''));
        Setting::set('fee_tabby_percent', number_format((float) $validated['fee_tabby_percent'], 2, '.', ''));
        Setting::set('fee_tamara_percent', number_format((float) $validated['fee_tamara_percent'], 2, '.', ''));

        return redirect()
            ->route('settings.payment')
            ->with('success', '✅ تم حفظ وتحديث نسب رسوم الدفع بنجاح. سيتم تطبيق النسب الجديدة فوراً على جميع كروت الحجز وروابط الدفع.');
    }
}
