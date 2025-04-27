<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool { return Auth::check() && Auth::user()->role === 'Admin'; }

    public function rules(): array
    {
        // دالة مخصصة للتحقق من أن الموعد النهائي قبل موعد الفعالية
        $afterEventDateRule = function ($attribute, $value, $fail) {
            $eventDate = $this->input('event_date');
            if ($eventDate && $value && strtotime($value) >= strtotime($eventDate)) {
                $fail('موعد انتهاء التسجيل يجب أن يكون قبل تاريخ الفعالية.');
            }
        };

        return [
            'event_name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'event_date' => ['required', 'date_format:Y-m-d\TH:i', 'after_or_equal:now'], // يجب أن يكون تاريخ ووقت صالحين وبعد الآن
            'location' => ['nullable', 'string', 'max:255'],
            'requires_registration' => ['sometimes', 'boolean'], // استخدام boolean للتحقق من 1/0 أو true/false
            'registration_deadline' => [
                'nullable', // اختياري
                'required_if:requires_registration,1,true', // مطلوب فقط إذا كان requires_registration هو true أو 1
                'date_format:Y-m-d\TH:i',
                'before:event_date', // يجب أن يكون قبل تاريخ الفعالية
                // $afterEventDateRule // يمكنك استخدام هذه القاعدة المخصصة أيضًا
            ],
        ];
    }
     // تحويل قيمة requires_registration إلى boolean قبل التحقق (إذا جاءت من checkbox)
    protected function prepareForValidation()
    {
        $this->merge([
            'requires_registration' => $this->boolean('requires_registration'),
        ]);
    }
}