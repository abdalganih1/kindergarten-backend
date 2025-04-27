<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\WeeklySchedule;
use App\Models\KindergartenClass; // لاختيار الفصل
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // لاستخدام Rule::unique
use App\Http\Requests\Admin\StoreWeeklyScheduleRequest; // يجب إنشاؤه
use App\Http\Requests\Admin\UpdateWeeklyScheduleRequest; // يجب إنشاؤه

class WeeklyScheduleController extends Controller
{
    /**
     * Display a listing of the weekly schedule entries.
     * عرض قائمة بمدخلات الجدول الأسبوعي مع الفلترة حسب الفصل واليوم.
     */
    public function index(Request $request)
    {
        $query = WeeklySchedule::with(['kindergartenClass', 'createdByAdmin.user']); // تحميل العلاقات

        // --- الفلترة ---
        // 1. حسب الفصل الدراسي
        $classId = $request->query('class_id');
        if ($classId) {
            $query->where('class_id', $classId);
        }

        // 2. حسب يوم الأسبوع
        $dayOfWeek = $request->query('day_of_week');
        if ($dayOfWeek) {
            $query->where('day_of_week', $dayOfWeek);
        }

        // --- الترتيب والـ Pagination ---
        // تحويل أيام الأسبوع إلى أرقام للترتيب الصحيح
        $daysOrder = array_flip(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']);
        $schedules = $query->get() // جلب كل النتائج أولاً للترتيب المخصص
                           ->sortBy(function($schedule) use ($daysOrder) {
                                // إنشاء مفتاح ترتيب يجمع بين اليوم والوقت
                                $dayOrder = $daysOrder[$schedule->day_of_week] ?? 7; // الأحد 0، السبت 6
                                return sprintf('%d-%s', $dayOrder, $schedule->start_time);
                            });

        // تطبيق Pagination يدويًا على المجموعة المرتبة
        $perPage = 15;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage('page');
        $currentPageItems = $schedules->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $paginatedSchedules = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            count($schedules),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );
        $paginatedSchedules->withQueryString(); // للحفاظ على الفلاتر

        // --- بيانات إضافية للـ View ---
        $classes = KindergartenClass::orderBy('class_name')->pluck('class_name', 'class_id');
        $daysOfWeek = [
            'Monday' => 'الإثنين', 'Tuesday' => 'الثلاثاء', 'Wednesday' => 'الأربعاء',
            'Thursday' => 'الخميس', 'Friday' => 'الجمعة', 'Saturday' => 'السبت', 'Sunday' => 'الأحد'
        ];

        return view('web.admin.schedules.index', compact(
            'paginatedSchedules', // إرسال النتيجة بعد الـ pagination
            'classes',
            'daysOfWeek',
            'classId',
            'dayOfWeek'
        ));
    }

    /**
     * Show the form for creating a new weekly schedule entry.
     * عرض نموذج إضافة مدخل جديد للجدول.
     */
    public function create(Request $request)
    {
        // جلب الفصول والأيام للنموذج
        $classes = KindergartenClass::orderBy('class_name')->pluck('class_name', 'class_id');
        $daysOfWeek = [
            'Monday' => 'الإثنين', 'Tuesday' => 'الثلاثاء', 'Wednesday' => 'الأربعاء',
            'Thursday' => 'الخميس', 'Friday' => 'الجمعة', 'Saturday' => 'السبت', 'Sunday' => 'الأحد'
        ];
        // أخذ الفصل المحدد مسبقًا من query parameter إذا وجد
        $selectedClassId = $request->query('class_id');

        return view('web.admin.schedules.create', compact('classes', 'daysOfWeek', 'selectedClassId'));
    }

    /**
     * Store a newly created weekly schedule entry in storage.
     * تخزين مدخل الجدول الجديد.
     *
     * @param  \App\Http\Requests\Admin\StoreWeeklyScheduleRequest  $request
     */
    public function store(StoreWeeklyScheduleRequest $request)
    {
        $validated = $request->validated();
        $admin = Auth::user()->adminProfile;

        if (!$admin) {
            return back()->with('error', 'Admin profile not found.')->withInput();
        }

        // التحقق من عدم التداخل الزمني (هذا التحقق تم نقله إلى Form Request Rule مخصصة)
        // إذا لم تستخدم Rule مخصصة، يمكنك إجراء التحقق هنا قبل الإنشاء

        WeeklySchedule::create([
            'class_id' => $validated['class_id'],
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'activity_description' => $validated['activity_description'],
            'created_by_id' => $admin->admin_id,
        ]);

        return redirect()->route('admin.schedules.index', ['class_id' => $validated['class_id'], 'day_of_week' => $validated['day_of_week']])
                         ->with('success', 'تمت إضافة النشاط للجدول بنجاح.');
    }

    /**
     * Display the specified resource. (Not typically needed)
     * عرض مدخل جدول واحد (غير ضروري عادةً).
     *
     * @param  \App\Models\WeeklySchedule  $weeklySchedule // استخدام اسم البارامتر الصحيح
     */
    public function show(WeeklySchedule $weeklySchedule)
    {
         $weeklySchedule->load(['kindergartenClass', 'createdByAdmin.user']);
         return view('web.admin.schedules.show', compact('weeklySchedule'));
    }

    /**
     * Show the form for editing the specified weekly schedule entry.
     * عرض نموذج تعديل مدخل جدول موجود.
     *
     * @param  \App\Models\WeeklySchedule  $weeklySchedule
     */
    public function edit(WeeklySchedule $weeklySchedule)
    {
        $classes = KindergartenClass::orderBy('class_name')->pluck('class_name', 'class_id');
        $daysOfWeek = [
            'Monday' => 'الإثنين', 'Tuesday' => 'الثلاثاء', 'Wednesday' => 'الأربعاء',
            'Thursday' => 'الخميس', 'Friday' => 'الجمعة', 'Saturday' => 'السبت', 'Sunday' => 'الأحد'
        ];

        return view('web.admin.schedules.edit', compact('weeklySchedule', 'classes', 'daysOfWeek'));
    }

    /**
     * Update the specified weekly schedule entry in storage.
     * تحديث مدخل الجدول.
     *
     * @param  \App\Http\Requests\Admin\UpdateWeeklyScheduleRequest  $request
     * @param  \App\Models\WeeklySchedule  $weeklySchedule
     */
    public function update(UpdateWeeklyScheduleRequest $request, WeeklySchedule $weeklySchedule)
    {
        $validated = $request->validated();

        // التحقق من عدم التداخل الزمني مع تجاهل السجل الحالي (تم نقله إلى Form Request Rule)

        $weeklySchedule->update([
             'class_id' => $validated['class_id'],
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'activity_description' => $validated['activity_description'],
            // لا نحدث created_by_id عادةً
        ]);

         return redirect()->route('admin.schedules.index', ['class_id' => $weeklySchedule->class_id, 'day_of_week' => $weeklySchedule->day_of_week])
                         ->with('success', 'تم تحديث النشاط في الجدول بنجاح.');
    }

    /**
     * Remove the specified weekly schedule entry from storage.
     * حذف مدخل الجدول.
     *
     * @param  \App\Models\WeeklySchedule  $weeklySchedule
     */
    public function destroy(WeeklySchedule $weeklySchedule)
    {
        try {
             // تخزين بيانات الفلترة قبل الحذف
             $classId = $weeklySchedule->class_id;
             $dayOfWeek = $weeklySchedule->day_of_week;

             $weeklySchedule->delete();
             return redirect()->route('admin.schedules.index', ['class_id' => $classId, 'day_of_week' => $dayOfWeek])
                             ->with('success', 'تم حذف النشاط من الجدول بنجاح.');
        } catch (\Exception $e) {
             return redirect()->route('admin.schedules.index')
                             ->with('error', 'فشل حذف النشاط من الجدول. يرجى المحاولة مرة أخرى.');
        }
    }
}