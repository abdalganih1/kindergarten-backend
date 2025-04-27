<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Rules\NoScheduleOverlap; // <-- استيراد القاعدة المخصصة

class StoreWeeklyScheduleRequest extends FormRequest
{
    public function authorize(): bool { return Auth::check() && Auth::user()->role === 'Admin'; }

    public function rules(): array
    {
        // أيام الأسبوع المسموح بها
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        return [
            'class_id' => ['required', 'integer', 'exists:kindergarten_classes,class_id'],
            'day_of_week' => ['required', 'string', Rule::in($days)],
            'start_time' => [
                'required',
                'date_format:H:i,H:i:s', // قبول تنسيق الساعة:الدقيقة أو ساعة:دقيقة:ثانية
                new NoScheduleOverlap() // <-- تطبيق القاعدة المخصصة
            ],
            'end_time' => ['required', 'date_format:H:i,H:i:s', 'after:start_time'], // وقت الانتهاء يجب أن يكون بعد وقت البدء
            'activity_description' => ['required', 'string', 'max:255'],
        ];
    }

    // تحضير بيانات الوقت قبل التحقق (لضمان تنسيق ثابت H:i:s)
    protected function prepareForValidation()
    {
        if ($this->start_time) {
            try { $this->merge(['start_time' => \Carbon\Carbon::parse($this->start_time)->format('H:i:s')]); } catch (\Exception $e) {}
        }
         if ($this->end_time) {
            try { $this->merge(['end_time' => \Carbon\Carbon::parse($this->end_time)->format('H:i:s')]); } catch (\Exception $e) {}
        }
    }
}