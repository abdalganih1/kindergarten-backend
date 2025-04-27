<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateEducationalResourceRequest extends FormRequest
{
     public function authorize(): bool { return Auth::check() && Auth::user()->role === 'Admin'; }

    public function rules(): array
    {
         return [
            'title' => ['sometimes', 'required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'resource_type' => ['sometimes', 'required', 'string', Rule::in(['Video', 'Article', 'Game', 'Link'])],
            'url_or_path' => ['sometimes', 'required', 'string', 'max:255'],
            'target_age_min' => ['nullable', 'integer', 'min:0', 'max:18'],
            'target_age_max' => ['nullable', 'integer', 'min:0', 'max:18', 'gte:target_age_min'],
            'subject' => ['nullable', 'string', 'max:100'],
        ];
    }
}