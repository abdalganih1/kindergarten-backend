<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return Auth::check() && Auth::user()->role === 'Admin'; }
    public function rules(): array
    {
        $userId = $this->route('user')->id;
        $adminProfileId = $this->route('user')->adminProfile->admin_id ?? null;
        $parentProfileId = $this->route('user')->parentProfile->parent_id ?? null;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['sometimes', 'required', 'string', 'in:Admin,Parent,Supervisor'],
            'is_active' => ['sometimes', 'boolean'],
            // حقول المدير
             'admin_contact_email' => ['nullable', 'required_if:role,Admin', 'email', 'max:100', Rule::unique('admins', 'contact_email')->ignore($adminProfileId, 'admin_id')],
            'admin_contact_phone' => ['nullable', 'string', 'max:20'],
             // حقول ولي الأمر
             'parent_contact_email' => ['nullable', 'required_if:role,Parent', 'email', 'max:100', Rule::unique('parents', 'contact_email')->ignore($parentProfileId, 'parent_id')],
            'parent_contact_phone' => ['nullable', 'string', 'max:20'],
            'parent_address' => ['nullable', 'string', 'max:1000'],
        ];
    }
}