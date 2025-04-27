<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return Auth::check() && Auth::user()->role === 'Admin'; }
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', 'in:Admin,Parent,Supervisor'],
            'is_active' => ['sometimes', 'boolean'],
            // حقول المدير (تكون مطلوبة فقط إذا كان الدور Admin)
            'admin_contact_email' => ['nullable', 'required_if:role,Admin', 'email', 'max:100', 'unique:admins,contact_email'],
            'admin_contact_phone' => ['nullable', 'string', 'max:20'],
             // حقول ولي الأمر (تكون مطلوبة فقط إذا كان الدور Parent)
            'parent_contact_email' => ['nullable', 'required_if:role,Parent', 'email', 'max:100', 'unique:parents,contact_email'],
            'parent_contact_phone' => ['nullable', 'string', 'max:20'],
            'parent_address' => ['nullable', 'string', 'max:1000'],
            // أضف حقول المشرف إذا لزم الأمر
        ];
    }
}