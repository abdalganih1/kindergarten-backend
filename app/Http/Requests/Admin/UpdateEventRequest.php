<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool { return Auth::check() && Auth::user()->role === 'Admin'; }

    public function rules(): array
    {
         $afterEventDateRule = function ($attribute, $value, $fail) {
            $eventDate = $this->input('event_date', $this->route('event')->event_date); // استخدم التاريخ القديم إذا لم يتم إرسال الجديد
            if ($eventDate && $value && strtotime($value) >= strtotime($eventDate)) {
                $fail('موعد انتهاء التسجيل يجب أن يكون قبل تاريخ الفعالية.');
            }
        };

        return [
            'event_name' => ['sometimes', 'required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
             // عند التحديث، قد نسمح بتاريخ سابق نظريًا، لكن يفضل إبقاؤه after_or_equal:now أو تعديل المنطق
            'event_date' => ['sometimes', 'required', 'date_format:Y-m-d\TH:i'],
            'location' => ['nullable', 'string', 'max:255'],
            'requires_registration' => ['sometimes', 'boolean'],
             'registration_deadline' => [
                'nullable',
                 // مطلوب فقط إذا كان requires_registration المحدث أو القديم هو true
                'required_if:requires_registration,1,true',
                'date_format:Y-m-d\TH:i',
                'before:event_date',
               // $afterEventDateRule
            ],
        ];
    }

     protected function prepareForValidation()
    {
        if ($this->has('requires_registration')) {
             $this->merge([
                'requires_registration' => $this->boolean('requires_registration'),
            ]);
        }
    }
}