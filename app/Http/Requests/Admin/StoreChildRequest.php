<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // استيراد Rule

class StoreChildRequest extends FormRequest
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
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['nullable', 'string', 'in:Male,Female,Other'],
            'enrollment_date' => ['required', 'date'],
            'class_id' => ['nullable', 'integer', 'exists:kindergarten_classes,class_id'], // تأكد من وجود الفصل
            'allergies' => ['nullable', 'string', 'max:2000'],
            'medical_notes' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // صورة، صيغ مسموحة، حجم أقصى 2MB
            'parent_ids' => ['nullable', 'array'], // يجب أن يكون مصفوفة
            'parent_ids.*' => ['integer', 'exists:parents,parent_id'], // كل عنصر في المصفوفة يجب أن يكون ID لولي أمر موجود
        ];
    }
}