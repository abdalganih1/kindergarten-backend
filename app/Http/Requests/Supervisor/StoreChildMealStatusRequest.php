<?php

namespace App\Http\Requests\Supervisor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Child; // للتحقق من الطفل
use App\Models\DailyMeal; // للتحقق من الوجبة

class StoreChildMealStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * يتم التحقق من صلاحية المشرف على الطفل داخل المتحكم بعد التحقق من صحة البيانات.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'Supervisor';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // قائمة حالات التناول المسموح بها
        $consumptionStatuses = ['EatenWell', 'EatenSome', 'EatenLittle', 'NotEaten', 'Refused', 'Absent'];

        return [
            'date' => ['required', 'date_format:Y-m-d'], // تاريخ اليوم الذي يتم التسجيل له
            'class_id' => ['required', 'integer', 'exists:kindergarten_classes,class_id'], // الفصل الذي يتم التسجيل له
            'statuses' => ['required', 'array', 'min:1'], // يجب أن يكون هناك بيانات حالة واحدة على الأقل
            // التحقق من كل طفل في مصفوفة الحالات
            'statuses.*' => ['required', 'array'], // كل عنصر طفل يجب أن يكون مصفوفة من الوجبات
            // التحقق من كل وجبة داخل كل طفل
            'statuses.*.*' => ['required', 'array'], // كل عنصر وجبة يجب أن يكون مصفوفة (status, notes?)
            'statuses.*.*.status' => ['required', 'string', Rule::in($consumptionStatuses)], // التحقق من حالة التناول
            'statuses.*.*.notes' => ['nullable', 'string', 'max:1000'], // الملاحظات اختيارية
        ];
    }

     public function messages()
    {
        return [
            'statuses.required' => 'يجب إدخال حالة واحدة على الأقل.',
            'statuses.array' => 'حدث خطأ في بيانات الحالات.',
            'statuses.*.*.status.required' => 'يجب تحديد حالة التناول لكل وجبة.',
            'statuses.*.*.status.in' => 'قيمة حالة التناول غير صالحة.',
            'statuses.*.*.notes.max' => 'الملاحظات يجب ألا تتجاوز 1000 حرف.',
        ];
    }
}