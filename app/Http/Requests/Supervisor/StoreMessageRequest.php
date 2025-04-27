<?php
namespace App\Http\Requests\Supervisor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\User; // للتحقق من وجود المستلم

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        // السماح للمشرف المسجل دخوله فقط
        return Auth::check() && Auth::user()->role === 'Supervisor';
    }

    public function rules(): array
    {
        // جلب المستخدمين الذين يمكن للمشرف مراسلتهم (يمكن تحسين الأداء هنا بتمرير القائمة)
        $supervisor = Auth::user();
        $supervisorClassIds = collect(); // TODO: Implement supervisor class scoping
        // $supervisorClassIds = $supervisor->supervisorClasses()->pluck('class_id');

         $messageableUserIds = User::where(function($q) use ($supervisorClassIds, $supervisor) {
                                    // Parents in supervised classes
                                    $q->where('role', 'Parent')
                                      ->whereHas('parentProfile.children', function ($childQuery) use ($supervisorClassIds) {
                                          $childQuery->whereIn('class_id', $supervisorClassIds);
                                      });
                                })
                                ->orWhere(function($q) use ($supervisor){ // Admins (excluding self)
                                     $q->where('role', 'Admin')->where('id', '!=', $supervisor->id);
                                })
                                ->pluck('id'); // الحصول على IDs فقط

        return [
            'recipient_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'), // التأكد من وجود المستخدم
                Rule::in($messageableUserIds), // التأكد من أنه ضمن المسموح لهم
                'different:sender_id', // يجب أن يكون مختلفًا عن المرسل (الذي هو المشرف)
            ],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages()
    {
        return [
            'recipient_id.in' => 'لا يمكنك إرسال رسالة لهذا المستخدم.',
            'recipient_id.different' => 'لا يمكنك إرسال رسالة لنفسك.',
        ];
    }

     // إضافة sender_id للتحقق من different
     protected function prepareForValidation()
     {
         $this->merge(['sender_id' => Auth::id()]);
     }
}