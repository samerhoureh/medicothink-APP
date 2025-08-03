<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'plan_id' => 'required|exists:subscription_plans,id',
            'payment_method' => 'sometimes|string|in:payclick',
        ];
    }

    public function messages()
    {
        return [
            'plan_id.required' => 'الباقة مطلوبة',
            'plan_id.exists' => 'الباقة غير موجودة',
            'payment_method.in' => 'طريقة الدفع غير مدعومة',
        ];
    }
}