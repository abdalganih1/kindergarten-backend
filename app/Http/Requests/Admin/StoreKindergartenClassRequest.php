<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreKindergartenClassRequest extends FormRequest
{
    public function authorize(): bool { return Auth::check() && Auth::user()->role === 'Admin'; }

    public function rules(): array
    {
        return [
            'class_name' => ['required', 'string', 'max:100', 'unique:kindergarten_classes,class_name'], // اسم الفصل يجب أن يكون فريدًا
            'description' => ['nullable', 'string', 'max:1000'],
            'min_age' => ['nullable', 'integer', 'min:0', 'max:18'],
            'max_age' => ['nullable', 'integer', 'min:0', 'max:18', 'gte:min_age'], // يجب أن يكون أكبر من أو يساوي العمر الأدنى
        ];
    }

     public function messages() // رسائل تحقق مخصصة (اختياري)
    {
        return [
            'class_name.unique' => 'اسم الفصل موجود بالفعل.',
            'max_age.gte' => 'العمر الأقصى يجب أن يكون أكبر من أو يساوي العمر الأدنى.',
        ];
    }
}