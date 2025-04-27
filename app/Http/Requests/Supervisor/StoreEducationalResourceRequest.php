<?php

namespace App\Http\Requests\Supervisor; // تأكد من الـ namespace الصحيح

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreEducationalResourceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * تحديد ما إذا كان المستخدم (المشرف) مصرحًا له بإنشاء هذا المورد.
     */
    public function authorize(): bool
    {
        // السماح فقط للمشرف المسجل دخوله بإنشاء المصادر
        return Auth::check() && Auth::user()->role === 'Supervisor';
        // يمكنك إضافة شروط أكثر تحديدًا هنا إذا لزم الأمر بناءً على صلاحيات معينة
    }

    /**
     * Get the validation rules that apply to the request.
     * الحصول على قواعد التحقق من الصحة التي تنطبق على الطلب.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // نفس قواعد التحقق المستخدمة لإنشاء المصدر من قبل المدير
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'resource_type' => ['required', 'string', Rule::in(['Video', 'Article', 'Game', 'Link'])],
            'url_or_path' => ['required', 'string', 'max:255'], // قد تحتاج لتحقق URL أكثر تحديدًا
            'target_age_min' => ['nullable', 'integer', 'min:0', 'max:18'],
            'target_age_max' => ['nullable', 'integer', 'min:0', 'max:18', 'gte:target_age_min'], // أكبر من أو يساوي العمر الأدنى
            'subject' => ['nullable', 'string', 'max:100'],
            // لا نتحقق من added_by_id هنا لأنه يتم تعيينه في المتحكم
        ];
    }

     /**
      * Get custom messages for validator errors.
      * رسائل خطأ مخصصة (اختياري)
      *
      * @return array
      */
    public function messages(): array
    {
        return [
            'title.required' => 'حقل العنوان مطلوب.',
            'title.max' => 'يجب ألا يتجاوز العنوان 200 حرف.',
            'resource_type.required' => 'يجب اختيار نوع المصدر.',
            'resource_type.in' => 'نوع المصدر المحدد غير صالح.',
            'url_or_path.required' => 'حقل الرابط أو المسار مطلوب.',
            'target_age_max.gte' => 'العمر الأقصى المستهدف يجب أن يكون أكبر من أو يساوي العمر الأدنى.',
            '*.integer' => 'يجب أن يكون الحقل رقمًا صحيحًا.',
            '*.min' => 'القيمة المدخلة صغيرة جدًا.',
            '*.max' => 'القيمة المدخلة كبيرة جدًا.',
        ];
    }

    /**
      * Get custom attributes for validator errors.
      * أسماء مخصصة للحقول في رسائل الخطأ (اختياري)
      *
      * @return array
      */
     public function attributes(): array
     {
         return [
             'title' => 'العنوان',
             'description' => 'الوصف',
             'resource_type' => 'نوع المصدر',
             'url_or_path' => 'الرابط أو المسار',
             'target_age_min' => 'العمر الأدنى',
             'target_age_max' => 'العمر الأقصى',
             'subject' => 'الموضوع',
         ];
     }
}