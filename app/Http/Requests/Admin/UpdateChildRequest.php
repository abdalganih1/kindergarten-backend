<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateChildRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'Admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // القواعد مشابهة لـ Store ولكن قد تكون اختيارية (sometimes)
        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name' => ['sometimes', 'required', 'string', 'max:100'],
            'date_of_birth' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
            'gender' => ['nullable', 'string', 'in:Male,Female,Other'],
            'enrollment_date' => ['sometimes', 'required', 'date'],
            'class_id' => ['nullable', 'integer', 'exists:kindergarten_classes,class_id'],
            'allergies' => ['nullable', 'string', 'max:2000'],
            'medical_notes' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'remove_photo' => ['sometimes', 'boolean'], // حقل إضافي للتحقق من إزالة الصورة
            'parent_ids' => ['sometimes', 'array'], // اجعلها sometimes للسماح بعدم التحديث
            'parent_ids.*' => ['integer', 'exists:parents,parent_id'],
        ];
    }
}