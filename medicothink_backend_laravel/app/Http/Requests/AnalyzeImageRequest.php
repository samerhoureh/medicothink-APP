<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeImageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:10240', // 10MB max
            'question' => 'sometimes|string|max:500',
            'conversation_id' => 'sometimes|exists:conversations,id',
        ];
    }

    public function messages()
    {
        return [
            'image.required' => 'الصورة مطلوبة',
            'image.image' => 'يجب أن يكون الملف صورة',
            'image.mimes' => 'نوع الصورة غير مدعوم',
            'image.max' => 'حجم الصورة كبير جداً',
            'question.max' => 'السؤال طويل جداً',
            'conversation_id.exists' => 'المحادثة غير موجودة',
        ];
    }
}