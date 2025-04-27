<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateKindergartenClassRequest extends FormRequest
{
     public function authorize(): bool { return Auth::check() && Auth::user()->role === 'Admin'; }

    public function rules(): array
    {
        // الحصول على ID الفصل الحالي من المسار لتجاهله عند التحقق من التفرد
        $classId = $this->route('kindergartenClass')->class_id; // استخدام اسم البارامتر الصحيح

        return [
             // sometimes مطلوب فقط إذا تم إرساله
            'class_name' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('kindergarten_classes', 'class_name')->ignore($classId, 'class_id')],
            'description' => ['nullable', 'string', 'max:1000'],
            'min_age' => ['nullable', 'integer', 'min:0', 'max:18'],
            'max_age' => ['nullable', 'integer', 'min:0', 'max:18', 'gte:min_age'],
        ];
    }
     public function messages()
    {
        return [
            'class_name.unique' => 'اسم الفصل موجود بالفعل.',
            'max_age.gte' => 'العمر الأقصى يجب أن يكون أكبر من أو يساوي العمر الأدنى.',
        ];
    }
}