<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateAnnouncementRequest extends FormRequest
{
     public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'Admin';
    }

    public function rules(): array
    {
         return [
            'title' => ['sometimes', 'required', 'string', 'max:200'], // sometimes لأننا نحدث
            'content' => ['sometimes', 'required', 'string', 'max:5000'],
            'target_class_id' => ['nullable', 'integer', 'exists:kindergarten_classes,class_id'],
        ];
    }
}