<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'phone_number' => 'required|string|max:20',
            'code' => 'required|string|size:6',
        ];
    }

    public function messages()
    {
        return [
            'phone_number.required' => 'رقم الهاتف مطلوب',
            'code.required' => 'رمز التحقق مطلوب',
            'code.size' => 'رمز التحقق يجب أن يكون 6 أرقام',
        ];
    }
}