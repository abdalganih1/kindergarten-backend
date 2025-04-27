<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Rules\NoScheduleOverlap; // <-- استيراد القاعدة المخصصة

class UpdateWeeklyScheduleRequest extends FormRequest
{
    public function authorize(): bool { return Auth::check() && Auth::user()->role === 'Admin'; }

    public function rules(): array
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $scheduleIdToIgnore = $this->route('weeklySchedule')->schedule_id; // الحصول على ID السجل الحالي

        return [
            'class_id' => ['sometimes', 'required', 'integer', 'exists:kindergarten_classes,class_id'],
            'day_of_week' => ['sometimes', 'required', 'string', Rule::in($days)],
            'start_time' => [
                'sometimes',
                'required',
                'date_format:H:i,H:i:s',
                new NoScheduleOverlap($scheduleIdToIgnore) // <-- تمرير ID السجل الحالي لتجاهله
            ],
            'end_time' => ['sometimes', 'required', 'date_format:H:i,H:i:s', 'after:start_time'],
            'activity_description' => ['sometimes', 'required', 'string', 'max:255'],
        ];
    }
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