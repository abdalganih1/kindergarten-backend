<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // استيراد Rule
class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool { return Auth::check() && Auth::user()->role === 'Admin'; }
    public function rules(): array
    {
        return [
            // لا نحتاج لتحديث التاريخ أو الطفل هنا عادةً
            'status' => ['required', 'string', Rule::in(['Present', 'Absent', 'Late', 'Excused'])],
            'notes' => 'nullable|string|max:1000',
            'check_in_time' => 'nullable|date_format:H:i,H:i:s',
            'check_out_time' => 'nullable|date_format:H:i,H:i:s',
        ];
    }
}