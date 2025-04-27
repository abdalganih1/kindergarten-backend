<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\WeeklySchedule;
use Illuminate\Support\Facades\Request; // للوصول إلى بيانات الطلب الأخرى

class NoScheduleOverlap implements ValidationRule
{
    protected $scheduleIdToIgnore = null; // لتجاهل السجل الحالي عند التحديث

    /**
     * Create a new rule instance.
     *
     * @param int|null $scheduleIdToIgnore The ID of the schedule entry being updated (null for create)
     */
    public function __construct($scheduleIdToIgnore = null)
    {
        $this->scheduleIdToIgnore = $scheduleIdToIgnore;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // الحصول على بيانات أخرى من الطلب (الفصل، اليوم، وقت البدء)
        $classId = Request::input('class_id');
        $dayOfWeek = Request::input('day_of_week');
        $startTime = Request::input('start_time'); // هذا هو $value غالبًا إذا طبقنا القاعدة على start_time
        $endTime = Request::input('end_time');

        // التأكد من وجود كل البيانات اللازمة للتحقق
        if (!$classId || !$dayOfWeek || !$startTime || !$endTime) {
            // لا يمكن التحقق إذا كانت الحقول الأخرى مفقودة (قواعد أخرى ستلتقط ذلك)
            return;
        }

        // بناء الاستعلام للبحث عن تداخل
        $query = WeeklySchedule::where('class_id', $classId)
                               ->where('day_of_week', $dayOfWeek)
                               ->where(function ($q) use ($startTime, $endTime) {
                                   // الحالة 1: وقت البدء الجديد يقع ضمن فترة موجودة
                                   $q->where(function ($sub) use ($startTime) {
                                       $sub->where('start_time', '<=', $startTime)
                                           ->where('end_time', '>', $startTime);
                                   })
                                   // الحالة 2: وقت الانتهاء الجديد يقع ضمن فترة موجودة
                                   ->orWhere(function ($sub) use ($endTime) {
                                       $sub->where('start_time', '<', $endTime)
                                           ->where('end_time', '>=', $endTime);
                                   })
                                   // الحالة 3: الفترة الجديدة تحتوي بالكامل على فترة موجودة
                                   ->orWhere(function ($sub) use ($startTime, $endTime) {
                                        $sub->where('start_time', '>=', $startTime)
                                            ->where('end_time', '<=', $endTime);
                                   });
                               });

        // تجاهل السجل الحالي عند التحديث
        if ($this->scheduleIdToIgnore) {
            $query->where('schedule_id', '!=', $this->scheduleIdToIgnore);
        }

        // التحقق من وجود أي تداخل
        if ($query->exists()) {
            $fail('يوجد تداخل زمني مع نشاط آخر في نفس اليوم ونفس الفصل.');
        }
    }
}