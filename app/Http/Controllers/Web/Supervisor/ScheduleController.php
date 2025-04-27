<?php

namespace App\Http\Controllers\Web\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\WeeklySchedule;
use App\Models\KindergartenClass; // للفلترة
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator; // لاستخدام الـ Pagination اليدوي
use Illuminate\Support\Collection; // لاستخدام Collection::sortBy

class ScheduleController extends Controller
{
    /**
     * Get the classes supervised by the current user.
     */
    private function getSupervisorClassIds()
    {
        $user = Auth::user();
        // TODO: Implement supervisor class scoping correctly
        return KindergartenClass::pluck('class_id'); // Example
        // return $user->supervisorClasses()->pluck('class_id');
    }

    /**
     * Display a listing of the weekly schedule entries for supervised classes.
     */
    public function index(Request $request)
    {
        $supervisorClassIds = $this->getSupervisorClassIds();

        if ($supervisorClassIds->isEmpty()) {
             return view('web.supervisor.schedules.index', [
               'paginatedSchedules' => new LengthAwarePaginator([], 0, 15),
               'supervisedClasses' => collect(),
               'daysOfWeek' => [],
               'classId' => null,
               'dayOfWeek' => null,
               'noClassesAssigned' => true
           ]);
        }

        $query = WeeklySchedule::with(['kindergartenClass', 'createdByAdmin.user'])
                               ->whereIn('class_id', $supervisorClassIds); // *** فلترة حسب فصول المشرف ***

        // --- الفلترة ---
        // 1. حسب الفصل الدراسي (من ضمن فصول المشرف)
        $classId = $request->query('class_id');
        if ($classId && $supervisorClassIds->contains($classId)) {
            $query->where('class_id', $classId);
        } elseif ($classId) {
             $query->whereRaw('1 = 0'); // لا تعرض شيئًا
        }

        // 2. حسب يوم الأسبوع
        $dayOfWeek = $request->query('day_of_week');
        if ($dayOfWeek) {
            $query->where('day_of_week', $dayOfWeek);
        }

        // --- الترتيب والـ Pagination اليدوي (كما في متحكم المدير) ---
        $daysOrder = array_flip(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']);
        $schedules = $query->get()
                           ->sortBy(function($schedule) use ($daysOrder) {
                                $dayOrder = $daysOrder[$schedule->day_of_week] ?? 7;
                                return sprintf('%d-%s', $dayOrder, $schedule->start_time);
                            });

        $perPage = 15;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage('page');
        $currentPageItems = $schedules->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $paginatedSchedules = new LengthAwarePaginator(
            $currentPageItems, count($schedules), $perPage, $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );
        $paginatedSchedules->withQueryString();

        // --- بيانات إضافية للـ View ---
        $supervisedClasses = KindergartenClass::whereIn('class_id', $supervisorClassIds)->orderBy('class_name')->pluck('class_name', 'class_id');
        $daysOfWeek = [
            'Monday' => 'الإثنين', 'Tuesday' => 'الثلاثاء', 'Wednesday' => 'الأربعاء',
            'Thursday' => 'الخميس', 'Friday' => 'الجمعة', 'Saturday' => 'السبت', 'Sunday' => 'الأحد'
        ];

        return view('web.supervisor.schedules.index', compact(
            'paginatedSchedules',
            'supervisedClasses',
            'daysOfWeek',
            'classId',
            'dayOfWeek'
        ));
    }

    /**
     * Display the specified weekly schedule entry (if supervisor has access).
     *
     * @param  \App\Models\WeeklySchedule  $weeklySchedule // اسم المتغير الصحيح للمسار
     */
    public function show(WeeklySchedule $weeklySchedule) // استخدام Route Model Binding
    {
        // *** التحقق من الصلاحية: هل الجدول لفصل يشرف عليه؟ ***
        $supervisorClassIds = $this->getSupervisorClassIds();
        if (!$supervisorClassIds->contains($weeklySchedule->class_id)) {
             abort(403, 'Unauthorized access to this schedule entry.');
        }

        $weeklySchedule->load(['kindergartenClass', 'createdByAdmin.user']);
        $daysOfWeek = [ /* ... نفس مصفوفة الأيام ... */ 'Monday' => 'الإثنين', 'Tuesday' => 'الثلاثاء', 'Wednesday' => 'الأربعاء', 'Thursday' => 'الخميس', 'Friday' => 'الجمعة', 'Saturday' => 'السبت', 'Sunday' => 'الأحد'];

        return view('web.supervisor.schedules.show', compact('weeklySchedule', 'daysOfWeek'));
    }

    // الدوال create, store, edit, update, destroy غير مطلوبة للمشرف هنا
}