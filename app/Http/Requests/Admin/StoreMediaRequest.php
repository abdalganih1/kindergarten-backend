<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\File; // لاستخدام قواعد التحقق من الملفات الجديدة

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool { return Auth::check() && Auth::user()->role === 'Admin'; }

    public function rules(): array
    {
        return [
            // التحقق من مصفوفة الملفات
            'media_files' => ['required', 'array', 'min:1'], // يجب أن يكون مصفوفة وتحتوي على ملف واحد على الأقل
            // التحقق من كل ملف داخل المصفوفة
            'media_files.*' => [
                'required',
                'file', // يجب أن يكون ملفًا
                // استخدام File rule لتحديد الأنواع والحجم
                File::types(['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'avi', 'wmv']) // الأنواع المسموحة للصور والفيديو
                    ->max(20 * 1024), // الحد الأقصى للحجم (مثال: 20 ميجابايت)
            ],
             // الحقول الأخرى اختيارية
            'description' => ['nullable', 'string', 'max:2000'],
            'associated_child_id' => ['nullable', 'integer', 'exists:children,child_id'],
            'associated_event_id' => ['nullable', 'integer', 'exists:events,event_id'],
            'associated_class_id' => ['nullable', 'integer', 'exists:kindergarten_classes,class_id'],
        ];
    }

     public function messages()
    {
        return [
            'media_files.required' => 'يجب اختيار ملف واحد على الأقل للرفع.',
            'media_files.array' => 'حدث خطأ غير متوقع في رفع الملفات.',
            'media_files.min' => 'يجب اختيار ملف واحد على الأقل للرفع.',
            'media_files.*.required' => 'أحد الملفات المرفوعة غير صالح أو مفقود.',
            'media_files.*.file' => 'أحد الملفات المرفوعة ليس ملفًا صالحًا.',
            'media_files.*.mimes' => 'أحد الملفات المرفوعة ذو نوع غير مدعوم.', // رسالة عامة لأن File::types لا تدعم رسائل Mimes مباشرة
             'media_files.*.max' => 'حجم أحد الملفات المرفوعة يتجاوز الحد المسموح به (20 ميجابايت).', // اضبط الرسالة حسب الحد الأقصى
        ];
    }
}