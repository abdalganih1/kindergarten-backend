<?php
namespace App\Http\Requests\Supervisor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\HealthRecord; // نحتاجه للتحقق من وجود السجل

class UpdateHealthRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
         // التحقق فقط من أن المستخدم مشرف ومسجل دخوله
         // (Route Model Binding سيهتم بالتحقق من وجود السجل)
         return Auth::check() && Auth::user()->role === 'Supervisor';
    }

    public function rules(): array
    {
        $recordTypes = ['Vaccination', 'Checkup', 'Illness', 'MedicationAdministered'];
        return [
            'record_type' => ['sometimes', 'required', 'string', Rule::in($recordTypes)],
            'record_date' => ['sometimes', 'required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'details' => ['sometimes', 'required', 'string', 'max:5000'],
            'next_due_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:record_date'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'remove_document' => ['sometimes', 'boolean'],
        ];
    }
}