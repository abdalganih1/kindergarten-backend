<?php

namespace App\Http\Controllers\Web\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\KindergartenClass;
use App\Models\Child;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Message; // لعرض أحدث الرسائل
use App\Models\Observation; // لعرض أحدث الملاحظات
use Carbon\Carbon;

class DashboardController extends Controller
{
     /**
     * Get the classes supervised by the current user.
     * (Helper function - adapt based on your actual implementation)
     * @return \Illuminate\Support\Collection
     */
    private function getSupervisorClassIds()
    {
        $user = Auth::user();
        // TODO: Implement supervisor class scoping correctly
        // Example: Fetching all for now, replace with actual logic
         return KindergartenClass::pluck('class_id');
        // return $user->supervisorClasses()->pluck('class_id');
    }

    /**
     * Display the supervisor dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $supervisorClassIds = $this->getSupervisorClassIds();

        // إذا لم يكن المشرف مسؤولاً عن أي فصول
        if ($supervisorClassIds->isEmpty()) {
             return view('web.supervisor.dashboard', ['noClassesAssigned' => true]);
        }

        // --- إحصائيات أساسية للفصول المشرف عليها ---
        $stats = [
            'total_supervised_children' => Child::whereIn('class_id', $supervisorClassIds)->count(),
            'total_supervised_classes' => $supervisorClassIds->count(),
        ];

        // --- إحصائيات الحضور لليوم الحالي (فصول المشرف فقط) ---
        $today = now()->toDateString();
        $attendanceTodayQuery = Attendance::whereDate('attendance_date', $today)
                                          ->whereHas('child', function ($q) use ($supervisorClassIds) {
                                              $q->whereIn('class_id', $supervisorClassIds);
                                          });

        // نسخ الاستعلام قبل تطبيق count لتجنب إعادة بنائه
        $attendanceToday = [
            'present' => (clone $attendanceTodayQuery)->where('status', 'Present')->count(),
            'absent' => (clone $attendanceTodayQuery)->where('status', 'Absent')->count(),
            'late' => (clone $attendanceTodayQuery)->where('status', 'Late')->count(),
            'total_recorded' => (clone $attendanceTodayQuery)->count(),
            'total_supervised' => $stats['total_supervised_children'], // إجمالي أطفال المشرف للمقارنة
        ];
        $attendanceToday['present_percentage'] = $attendanceToday['total_recorded'] > 0
            ? round(($attendanceToday['present'] / $attendanceToday['total_recorded']) * 100)
            : ($attendanceToday['total_supervised'] > 0 ? 0 : 100); // اعتبر النسبة 100% إذا لا يوجد أطفال أصلًا


        // --- أحدث الأنشطة المتعلقة بالمشرف ---
        $recentActivities = [
            // أحدث الملاحظات من أولياء أمور أطفال فصوله (أو الملاحظات العامة؟)
            'latest_observations' => Observation::with(['parentSubmitter.user', 'child'])
                                       ->where(function($q) use ($supervisorClassIds) {
                                            $q->whereNull('child_id') // ملاحظات عامة
                                              ->orWhereHas('child', function($childQuery) use ($supervisorClassIds){
                                                    $childQuery->whereIn('class_id', $supervisorClassIds);
                                                });
                                        })
                                       ->latest('submitted_at')
                                       ->take(5)
                                       ->get(),

            // أحدث الرسائل الواردة للمشرف (أو الصادرة منه؟)
            'latest_messages' => Message::with(['sender', 'recipient'])
                                  ->where('recipient_id', $user->id) // الرسائل الواردة للمشرف
                                  // ->orWhere('sender_id', $user->id) // يمكنك إضافة الصادرة إذا أردت
                                  ->latest('sent_at')
                                  ->take(5)
                                  ->get(),

             // الفعاليات القادمة (كل الفعاليات أو المتعلقة بفصوله فقط؟ - نعرض الكل الآن)
             'upcoming_events' => Event::where('event_date', '>=', now())
                                     ->orderBy('event_date', 'asc')
                                     ->take(5)
                                     ->get(),

            // أطفال لم يتم تسجيل حضورهم اليوم (إذا كان إجمالي المسجل أقل من الإجمالي)
            'missing_attendance_children' => ($attendanceToday['total_recorded'] < $attendanceToday['total_supervised'])
                ? Child::whereIn('class_id', $supervisorClassIds)
                       ->whereDoesntHave('attendances', function ($q) use ($today) {
                           $q->whereDate('attendance_date', $today);
                       })
                       ->orderBy('first_name')
                       ->take(10) // عرض أول 10 فقط
                       ->get(['child_id', 'first_name', 'last_name', 'class_id'])
                : collect(), // مجموعة فارغة إذا تم تسجيل الجميع
        ];

        // قائمة الفصول التي يشرف عليها لعرضها في مكان ما بالواجهة
        $supervisedClasses = KindergartenClass::whereIn('class_id', $supervisorClassIds)->orderBy('class_name')->get();


        return view('web.supervisor.dashboard', compact(
            'stats',
            'attendanceToday',
            'recentActivities',
            'supervisedClasses' // قائمة الفصول
        ));
    }
}