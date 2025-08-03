<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateFlashcardsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'topic' => 'required|string|max:200',
            'count' => 'sometimes|integer|min:1|max:20',
            'conversation_id' => 'sometimes|exists:conversations,id',
        ];
    }

    public function messages()
    {
        return [
            'topic.required' => 'موضوع البطاقات مطلوب',
            'topic.max' => 'موضوع البطاقات طويل جداً',
            'count.min' => 'عدد البطاقات يجب أن يكون على الأقل 1',
            'count.max' => 'عدد البطاقات يجب أن يكون 20 على الأكثر',
            'conversation_id.exists' => 'المحادثة غير موجودة',
        ];
    }
}