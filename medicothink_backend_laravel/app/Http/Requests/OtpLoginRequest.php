<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OtpLoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'phone_number' => 'required|string|max:20',
        ];
    }

    public function messages()
    {
        return [
            'phone_number.required' => 'رقم الهاتف مطلوب',
        ];
    }
}