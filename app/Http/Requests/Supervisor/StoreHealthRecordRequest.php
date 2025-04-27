<?php
namespace App\Http\Requests\Supervisor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Child; // ما زلنا نحتاجه للتحقق من وجود الطفل
use App\Models\KindergartenClass;

class StoreHealthRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user(); // الحصول على المستخدم الحالي

        // 1. التحقق فقط من أن المستخدم هو مشرف ومسجل دخوله
        if (!$user || $user->role !== 'Supervisor') {
            return false; // ليس مشرفًا
        }

        // 2. التحقق فقط من أن الطفل المرسل في الطلب موجود بالفعل
        // (تم إزالة التحقق من انتماء الطفل لفصل المشرف)
        if ($this->child_id) {
            return Child::where('child_id', $this->child_id)->exists(); // يكفي التحقق من وجود الطفل
        }

        // إذا لم يتم إرسال child_id، لا يمكن التصريح
        return false;
    }

    public function rules(): array
    {
         $recordTypes = ['Vaccination', 'Checkup', 'Illness', 'MedicationAdministered'];
        return [
            'child_id' => ['required', 'integer', 'exists:children,child_id'], // التحقق من الوجود في قاعدة البيانات
            'record_type' => ['required', 'string', Rule::in($recordTypes)],
            'record_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'details' => ['required', 'string', 'max:5000'],
            'next_due_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:record_date'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // Max 5MB
        ];
    }
}