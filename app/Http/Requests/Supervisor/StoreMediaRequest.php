<?php
namespace App\Http\Requests\Supervisor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\File;
use App\Models\Child; // للتحقق من الطفل
use App\Models\KindergartenClass; // للتحقق من الفصل

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
         // السماح للمشرف المسجل دخوله فقط
        if (!Auth::check() || !in_array(Auth::user()->role, ['Supervisor', 'Admin'])) { // يمكن السماح للمدير أيضًا
            return false;
        }

        // التحقق من صلاحية الربط (يمكن وضعه هنا أو في المتحكم)
        $supervisorClassIds = collect(); // TODO: Fetch supervisor classes
        // $supervisorClassIds = Auth::user()->supervisorClasses()->pluck('class_id');

        // التحقق من الفصل المختار
        if ($this->associated_class_id && !$supervisorClassIds->contains($this->associated_class_id)) {
             return false; // ليس له صلاحية على هذا الفصل
        }
        // التحقق من الطفل المختار
        if ($this->associated_child_id) {
            $childClass = Child::find($this->associated_child_id)?->class_id;
             if (!$childClass || !$supervisorClassIds->contains($childClass)) {
                 return false; // الطفل ليس في فصل يشرف عليه
             }
        }

        return true; // إذا اجتاز كل التحققات أو لم يتم الربط
    }

    public function rules(): array
    {
         // نفس قواعد المدير
        return [
            'media_files' => ['required', 'array', 'min:1'],
            'media_files.*' => ['required','file', File::types(['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'avi', 'wmv'])->max(20 * 1024)],
            'description' => ['nullable', 'string', 'max:2000'],
            'associated_child_id' => ['nullable', 'integer', 'exists:children,child_id'],
            'associated_event_id' => ['nullable', 'integer', 'exists:events,event_id'],
            'associated_class_id' => ['nullable', 'integer', 'exists:kindergarten_classes,class_id'],
        ];
    }
     public function messages()
    {
        // نفس رسائل المدير
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