<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id'      => 'required|exists:customers,id',
            'template_id'      => 'required|exists:contract_templates,id',
            'property_details' => 'required|string|max:2000',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after:start_date',
            'rent_amount'      => 'required|numeric|min:1|max:99999999.99',
            'payment_method'   => ['required', new Enum(PaymentMethod::class)],
            'additional_terms' => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required'      => 'يرجى اختيار العميل.',
            'customer_id.exists'        => 'العميل المحدد غير موجود.',
            'template_id.required'      => 'يرجى اختيار نموذج العقد.',
            'template_id.exists'        => 'نموذج العقد المحدد غير موجود.',
            'property_details.required' => 'وصف العقار مطلوب.',
            'property_details.max'      => 'وصف العقار يجب ألا يتجاوز 2000 حرف.',
            'start_date.required'       => 'تاريخ البداية مطلوب.',
            'start_date.date'           => 'صيغة تاريخ البداية غير صحيحة.',
            'end_date.required'         => 'تاريخ النهاية مطلوب.',
            'end_date.after'            => 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية.',
            'rent_amount.required'      => 'قيمة الإيجار مطلوبة.',
            'rent_amount.numeric'       => 'قيمة الإيجار يجب أن تكون رقماً.',
            'rent_amount.min'           => 'قيمة الإيجار يجب أن تكون أكبر من صفر.',
            'rent_amount.max'           => 'قيمة الإيجار تجاوزت الحد المسموح.',
            'payment_method.required'   => 'يرجى اختيار طريقة الدفع.',
            'additional_terms.max'      => 'الشروط الإضافية يجب ألا تتجاوز 5000 حرف.',
        ];
    }
}
