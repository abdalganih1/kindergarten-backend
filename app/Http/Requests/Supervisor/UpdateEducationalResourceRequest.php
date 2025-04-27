<?php
namespace App\Http\Requests\Supervisor; // تأكد من namespace الصحيح
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateEducationalResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // السماح للمشرف المسجل دخوله فقط
         return Auth::check() && Auth::user()->role === 'Supervisor';

         // أو أضف تحقق من الملكية هنا إذا أردت قصر التعديل على المنشئ
         // $resource = $this->route('educationalResource');
         // return Auth::check() && Auth::user()->role === 'Supervisor' /* && $resource->creator_id === Auth::id() */;
    }

    public function rules(): array
    {
         // نفس قواعد المدير أو قواعد مختلفة خاصة بالمشرف
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
     public function messages()
    {
        return [
            'max_age.gte' => 'العمر الأقصى يجب أن يكون أكبر من أو يساوي العمر الأدنى.',
        ];
    }
}