<?php
namespace App\Http\Requests\Supervisor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Attendance;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
         if (!Auth::check() || Auth::user()->role !== 'Supervisor') {
            return false;
        }
        // التحقق من أن المشرف له صلاحية على هذا السجل المحدد
        $attendance = $this->route('attendance'); // الحصول على السجل من المسار
        if ($attendance) {
            $supervisorClassIds = collect(); // استبدل بالطريقة الفعلية لجلب فصول المشرف
            // TODO: Implement supervisor class scoping logic here or fetch from service
             // Example: $supervisorClassIds = Auth::user()->supervisorClasses()->pluck('class_id');
            return $supervisorClassIds->contains($attendance->child->class_id);
        }
        return false; // لا تسمح إذا لم يتم العثور على السجل
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(['Present', 'Absent', 'Late', 'Excused'])],
            'notes' => 'nullable|string|max:1000',
            'check_in_time' => 'nullable|date_format:H:i,H:i:s',
            'check_out_time' => 'nullable|date_format:H:i,H:i:s',
        ];
    }
}