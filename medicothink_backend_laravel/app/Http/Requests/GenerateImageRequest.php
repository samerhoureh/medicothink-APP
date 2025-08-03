<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateImageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'prompt' => 'required|string|max:1000',
            'conversation_id' => 'sometimes|exists:conversations,id',
        ];
    }

    public function messages()
    {
        return [
            'prompt.required' => 'وصف الصورة مطلوب',
            'prompt.max' => 'وصف الصورة طويل جداً',
            'conversation_id.exists' => 'المحادثة غير موجودة',
        ];
    }
}