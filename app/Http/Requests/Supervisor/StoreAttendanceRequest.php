<?php
namespace App\Http\Requests\Supervisor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Child;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // السماح للمشرف المسجل دخوله فقط
        if (!Auth::check() || Auth::user()->role !== 'Supervisor') {
            return false;
        }

        // (اختياري) تحقق إضافي: هل المشرف له صلاحية على الأطفال المضمنين في الطلب؟
        // قد يكون من الأفضل إجراء هذا التحقق في المتحكم نفسه كما فعلنا
        // لأنه يتطلب جلب بيانات الأطفال والتحقق من فصولهم.
        return true; // التحقق الأساسي من الدور فقط هنا
    }

    public function rules(): array
    {
         // نفس قواعد المدير أو تعديلات طفيفة إذا لزم الأمر
        return [
            'attendance_date' => 'required|date_format:Y-m-d',
            'attendance' => 'required|array',
            'attendance.*.child_id' => 'required|integer|exists:children,child_id',
            'attendance.*.status' => ['required', 'string', Rule::in(['Present', 'Absent', 'Late', 'Excused'])],
            'attendance.*.notes' => 'nullable|string|max:1000',
            'attendance.*.check_in_time' => 'nullable|date_format:H:i,H:i:s',
            'attendance.*.check_out_time' => 'nullable|date_format:H:i,H:i:s',
        ];
    }
}