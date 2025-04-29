<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\KindergartenClass; // <-- استيراد نموذج الفصل

class UpdateChildRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'Admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxBirthDate = Carbon::now()->subYears(6)->format('Y-m-d');

        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name' => ['sometimes', 'required', 'string', 'max:100'],
            'date_of_birth' => [
                'sometimes',
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:' . $maxBirthDate,
            ],
            'gender' => ['nullable', 'string', 'in:Male,Female,Other'],
            'enrollment_date' => ['sometimes', 'required', 'date'],
            'class_id' => [
                'nullable',
                'integer',
                'exists:kindergarten_classes,class_id',
                // --- إضافة قاعدة التحقق من توافق العمر مع الفصل ---
                function ($attribute, $value, $fail) {
                     // الحصول على تاريخ الميلاد (إما الجديد أو القديم إذا لم يتم تغييره)
                    $birthDate = $this->input('date_of_birth', $this->route('child')->date_of_birth);
                    // التحقق فقط إذا كان تاريخ الميلاد والفصل موجودين وصالحين
                    if ($value && $birthDate) {
                         try {
                            $birthDateCarbon = Carbon::parse($birthDate);
                            $childAge = $birthDateCarbon->age;

                            $class = KindergartenClass::find($value);

                            if ($class && $class->min_age !== null && $childAge < $class->min_age) {
                                $fail("عمر الطفل ({$childAge}) أقل من الحد الأدنى للعمر المطلوب لهذا الفصل ({$class->min_age}).");
                            }
                            if ($class && $class->max_age !== null && $childAge > $class->max_age) {
                                 $fail("عمر الطفل ({$childAge}) أكبر من الحد الأقصى للعمر المسموح به لهذا الفصل ({$class->max_age}).");
                            }
                        } catch (\Exception $e) {
                            // تجاهل الخطأ هنا
                        }
                    }
                },
                // ---------------------------------------------
            ],
            'allergies' => ['nullable', 'string', 'max:2000'],
            'medical_notes' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'remove_photo' => ['sometimes', 'boolean'],
            'parent_ids' => ['sometimes', 'array'],
            'parent_ids.*' => ['integer', 'exists:parents,parent_id'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'date_of_birth.after_or_equal' => 'لا يمكن تعديل تاريخ الميلاد لطفل يزيد عمره عن 6 سنوات.',
        ];
    }
}