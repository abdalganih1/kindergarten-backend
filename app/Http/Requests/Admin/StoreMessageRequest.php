<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        // السماح للمدير المسجل دخوله فقط
        return Auth::check() && Auth::user()->role === 'Admin';
    }

    public function rules(): array
    {
        return [
            'recipient_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'), // التأكد من وجود المستخدم
                'different:sender_id', // يجب أن يكون مختلفًا عن المرسل (المدير الحالي)
            ],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ];
    }

     public function messages()
    {
        return [
            'recipient_id.different' => 'لا يمكنك إرسال رسالة لنفسك.',
            'recipient_id.exists' => 'المستخدم المستلم المحدد غير موجود.',
        ];
    }

     // إضافة sender_id للتحقق من different
     protected function prepareForValidation()
     {
         $this->merge(['sender_id' => Auth::id()]);
     }
}