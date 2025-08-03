<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateVideoRequest extends FormRequest
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
            'prompt.required' => 'وصف الفيديو مطلوب',
            'prompt.max' => 'وصف الفيديو طويل جداً',
            'conversation_id.exists' => 'المحادثة غير موجودة',
        ];
    }
}