<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $clientId = $this->route('client') ? $this->route('client')->id : null;

        return [
            'name' => 'required|string|max:255',
            'national_id' => 'required|string|unique:customers,national_id,' . $clientId,
            'phone' => 'required|string|min:10',
            'type' => 'required|in:lessee,lessor',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'اسم العميل مطلوب.',
            'national_id.required' => 'رقم الهوية مطلوب.',
            'national_id.unique' => 'رقم الهوية هذا مسجل مسبقاً في النظام.',
            'phone.required' => 'رقم الجوال مطلوب.',
        ];
    }
}
