<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Handle via Middleware/Policy
    }

    public function rules()
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'template_id' => 'required|exists:contract_templates,id',
            'property_details' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'rent_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:monthly,quarterly,semi_annual,annual',
            'additional_terms' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'هذا الحقل مطلوب.',
            'exists' => 'الخيار المحدد غير صالح.',
            'date' => 'صيغة التاريخ غير صحيحة.',
            'after' => 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية.',
            'numeric' => 'يجب أن يكون رقماً.',
        ];
    }
}
