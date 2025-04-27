<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'Admin';
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'content' => ['required', 'string', 'max:5000'], // زيادة الحد الأقصى إذا لزم الأمر
            'target_class_id' => ['nullable', 'integer', 'exists:kindergarten_classes,class_id'], // يجب أن يكون ID لفصل موجود أو null
        ];
    }
}