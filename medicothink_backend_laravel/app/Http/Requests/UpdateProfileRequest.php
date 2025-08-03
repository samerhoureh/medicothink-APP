<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $this->user()->id,
            'phone_number' => 'sometimes|string|max:20|unique:users,phone_number,' . $this->user()->id,
            'age' => 'sometimes|integer|min:1|max:120',
            'nationality' => 'sometimes|string|max:100',
            'region' => 'sometimes|string|max:100',
            'specialization' => 'sometimes|string|max:100',
            'education_level' => 'sometimes|string|max:100',
            'profile_image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
            'phone_number.unique' => 'رقم الهاتف مستخدم بالفعل',
            'profile_image.image' => 'يجب أن تكون صورة الملف الشخصي صورة صحيحة',
            'profile_image.max' => 'حجم صورة الملف الشخصي يجب أن يكون أقل من 2 ميجابايت',
        ];
    }
}