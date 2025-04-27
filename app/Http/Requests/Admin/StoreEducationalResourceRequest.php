<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreEducationalResourceRequest extends FormRequest
{
    public function authorize(): bool { return Auth::check() && Auth::user()->role === 'Admin'; }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'resource_type' => ['required', 'string', Rule::in(['Video', 'Article', 'Game', 'Link'])],
            'url_or_path' => ['required', 'string', 'max:255'], // قد تحتاج لتحقق URL إذا كان النوع Link أو Video
            'target_age_min' => ['nullable', 'integer', 'min:0', 'max:18'],
            'target_age_max' => ['nullable', 'integer', 'min:0', 'max:18', 'gte:target_age_min'], // يجب أن يكون أكبر من أو يساوي العمر الأدنى
            'subject' => ['nullable', 'string', 'max:100'],
        ];
    }

    // (اختياري) يمكنك إضافة رسائل تحقق مخصصة بالعربية
    // public function messages()
    // {
    //     return [
    //         'target_age_max.gte' => 'العمر الأقصى المستهدف يجب أن يكون أكبر من أو يساوي العمر الأدنى.',
    //         // ...
    //     ];
    // }
}