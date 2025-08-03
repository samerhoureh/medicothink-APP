<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'message' => 'required|string|max:2000',
            'conversation_id' => 'sometimes|exists:conversations,id',
        ];
    }

    public function messages()
    {
        return [
            'message.required' => 'الرسالة مطلوبة',
            'message.max' => 'الرسالة طويلة جداً',
            'conversation_id.exists' => 'المحادثة غير موجودة',
        ];
    }
}