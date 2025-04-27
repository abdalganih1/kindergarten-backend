<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password; // لاستخدام قواعد كلمة المرور

class RegisterParentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * (نسمح لأي شخص بالوصول لإنشاء حساب جديد)
     */
    public function authorize(): bool
    {
        return true; // أي شخص يمكنه محاولة التسجيل
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'], // اسم ولي الأمر الكامل
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'], // بريد فريد لتسجيل الدخول
            'password' => ['required', 'confirmed', Password::defaults()], // كلمة مرور قوية ومؤكدة
            // حقول ParentModel الاختيارية (يمكن إضافتها لاحقًا عبر تحديث الملف الشخصي)
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'contact_email' => ['nullable', 'string', 'email', 'max:100', 'unique:parents,contact_email'], // بريد اتصال فريد (اختياري)
        ];
    }
}