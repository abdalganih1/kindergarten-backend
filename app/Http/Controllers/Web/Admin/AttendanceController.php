<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Child;
use App\Models\KindergartenClass; // للفلترة حسب الفصل
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // للحصول على هوية المسجل
use Carbon\Carbon; // للتعامل مع التواريخ
use App\Http\Requests\Admin\StoreAttendanceRequest; // استخدم Form Request للتحقق
use App\Http\Requests\Admin\UpdateAttendanceRequest; // استخدم Form Request للتحقق
use Illuminate\Validation\Rule; // استيراد Rule

class AttendanceController extends Controller
{
    /**
     * Display a listing of the attendance records.
     * عرض قائمة بسجلات الحضور والغياب مع إمكانية الفلترة حسب التاريخ والفصل.
     */
    public function index(Request $request)
    {
        // --- الفلترة والبحث ---
        $query = Attendance::with(['child.kindergartenClass', 'recordedByUser']) // تحميل العلاقات اللازمة
                         ->select('attendances.*'); // تحديد الجدول لتجنب تضارب أسماء الأعمدة عند الربط

        // 1. الفلترة حسب التاريخ
        $selectedDate = $request->query('date', now()->format('Y-m-d')); // الافتراضي تاريخ اليوم
        try {
            // التحقق من صحة التاريخ قبل استخدامه
            $filterDate = Carbon::createFromFormat('Y-m-d', $selectedDate)->format('Y-m-d');
             $query->whereDate('attendance_date', $filterDate);
        } catch (\Exception $e) {
            // إذا كان التاريخ غير صالح، استخدم تاريخ اليوم
            $filterDate = now()->format('Y-m-d');
             $query->whereDate('attendance_date', $filterDate);
             // يمكنك إضافة رسالة خطأ للمستخدم هنا إذا أردت
             // return back()->with('error', 'Invalid date format. Showing records for today.');
        }


        // 2. الفلترة حسب الفصل الدراسي
        $selectedClassId = $request->query('class_id');
        if ($selectedClassId) {
            // استخدام join للفلترة حسب الفصل المرتبط بالطفل
             $query->join('children', 'attendances.child_id', '=', 'children.child_id')
                   ->where('children.class_id', $selectedClassId);
        }

        // 3. البحث عن اسم الطفل (اختياري)
        $searchTerm = $request->query('search');
        if ($searchTerm) {
            // التأكد من أننا قمنا بالربط مع جدول الأطفال إذا لم يتم الفلترة حسب الفصل
             if (!$selectedClassId) {
                 $query->join('children', 'attendances.child_id', '=', 'children.child_id');
             }
            // بحث في الاسم الأول أو الأخير
            $query->where(function($q) use ($searchTerm) {
                $q->where('children.first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('children.last_name', 'like', "%{$searchTerm}%");
            });
        }

        // --- الترتيب والـ Pagination ---
        $attendances = $query->latest('attendances.created_at') // أو الترتيب حسب اسم الطفل الخ
                             ->paginate(20) // عرض 20 سجلاً في الصفحة
                             ->withQueryString(); // للحفاظ على query parameters عند التنقل بين الصفحات

        // --- بيانات إضافية للـ View (قوائم الفلترة) ---
        $classes = KindergartenClass::orderBy('class_name')->pluck('class_name', 'class_id');

        // إرسال البيانات إلى الواجهة
        return view('web.admin.attendance.index', compact('attendances', 'classes', 'selectedDate', 'selectedClassId', 'searchTerm')); // تأكد من إنشاء هذا الـ view
    }

    /**
     * Show the form for creating a new attendance record (or batch recording).
     * عادةً ما يتم تسجيل الحضور لمجموعة من الأطفال في تاريخ معين، لذا قد يكون النموذج مختلفًا.
     * سنصمم هنا طريقة لإنشاء سجل واحد، ويمكن تطويرها لتسجيل دفعة.
     */
    public function create(Request $request)
    {
        // تاريخ اليوم افتراضيًا، يمكن تغييره عبر query parameter
        $attendanceDate = $request->query('date', now()->format('Y-m-d'));
        try {
            $attendanceDate = Carbon::createFromFormat('Y-m-d', $attendanceDate)->format('Y-m-d');
        } catch (\Exception $e) {
            $attendanceDate = now()->format('Y-m-d');
        }

        // جلب قائمة بالأطفال الذين ليس لديهم سجل حضور لهذا اليوم بعد
        $childrenWithoutAttendance = Child::whereDoesntHave('attendances', function ($query) use ($attendanceDate) {
                                             $query->whereDate('attendance_date', $attendanceDate);
                                         })
                                         ->orderBy('first_name')
                                         ->get(['child_id', 'first_name', 'last_name']); // تحديد الأعمدة المطلوبة

        // قائمة الحالات الممكنة للحضور
        $statuses = ['Present' => 'Present', 'Absent' => 'Absent', 'Late' => 'Late', 'Excused' => 'Excused'];

        // عرض واجهة إضافة سجل حضور
        return view('web.admin.attendance.create', compact('childrenWithoutAttendance', 'statuses', 'attendanceDate')); // تأكد من إنشاء هذا الـ view
    }


     /**
     * Show the form for recording attendance for a specific class on a specific date.
     * طريقة بديلة لعرض نموذج يسجل الحضور لكل أطفال فصل معين في يوم محدد.
     */
    public function createBatch(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'class_id' => 'required|integer|exists:kindergarten_classes,class_id',
        ]);

        $attendanceDate = $validated['date'];
        $classId = $validated['class_id'];

        $classInfo = KindergartenClass::find($classId);
        if (!$classInfo) {
            return back()->with('error', 'Class not found.');
        }

        // جلب أطفال الفصل مع حالة حضورهم لهذا اليوم (إن وجدت)
        $children = Child::where('class_id', $classId)
                         ->with(['attendances' => function ($query) use ($attendanceDate) {
                             $query->whereDate('attendance_date', $attendanceDate);
                         }])
                         ->orderBy('first_name')
                         ->get();

        $statuses = ['Present' => 'Present', 'Absent' => 'Absent', 'Late' => 'Late', 'Excused' => 'Excused'];

        // عرض واجهة تسجيل الحضور الجماعي
        return view('web.admin.attendance.create_batch', compact('children', 'statuses', 'attendanceDate', 'classInfo'));
    }

    /**
     * Store newly created attendance records in storage (handles batch saving).
     * يخزن سجلات الحضور الجديدة (سواء سجل واحد أو دفعة).
     *
     * @param  \Illuminate\Http\Request  $request // استخدام Request هنا لأنه يعالج مصفوفة بيانات
     */
    public function store(Request $request)
    {
        // التحقق الأساسي من التاريخ (التحقق التفصيلي لكل طفل يتم داخل الحلقة)
        $validatedDate = $request->validate([
            'attendance_date' => 'required|date_format:Y-m-d',
            'attendance' => 'required|array', // نتوقع مصفوفة من بيانات الحضور
            'attendance.*.child_id' => 'required|integer|exists:children,child_id',
            'attendance.*.status' => ['required', 'string', Rule::in(['Present', 'Absent', 'Late', 'Excused'])],
            'attendance.*.notes' => 'nullable|string|max:1000',
            'attendance.*.check_in_time' => 'nullable|date_format:H:i,H:i:s', // تنسيق الوقت
            'attendance.*.check_out_time' => 'nullable|date_format:H:i,H:i:s', // تنسيق الوقت
        ]);

        $attendanceDate = $validatedDate['attendance_date'];
        $attendanceData = $validatedDate['attendance'];
        $recordedById = Auth::id(); // هوية المستخدم الذي يسجل الحضور
        $errors = [];
        $successCount = 0;

        foreach ($attendanceData as $childAttendance) {
            try {
                // استخدام updateOrCreate لتحديث السجل إن وجد أو إنشائه إن لم يوجد
                Attendance::updateOrCreate(
                    [
                        'child_id' => $childAttendance['child_id'],
                        'attendance_date' => $attendanceDate,
                    ],
                    [
                        'status' => $childAttendance['status'],
                        'notes' => $childAttendance['notes'] ?? null,
                        'check_in_time' => $childAttendance['check_in_time'] ?? null,
                        'check_out_time' => $childAttendance['check_out_time'] ?? null,
                        'recorded_by_id' => $recordedById,
                    ]
                );
                $successCount++;
            } catch (\Exception $e) {
                // تسجيل الخطأ إذا فشل حفظ سجل معين
                 $childName = Child::find($childAttendance['child_id'])->full_name ?? $childAttendance['child_id']; // حاول الحصول على اسم الطفل
                $errors[] = "Failed to save attendance for child ID {$childName}: " . $e->getMessage();
                 \Log::error("Attendance save error for child {$childAttendance['child_id']} on {$attendanceDate}: " . $e->getMessage());
            }
        }

        // رسالة النجاح أو الخطأ
        $message = $successCount . " attendance records saved successfully for " . $attendanceDate . ".";
        if (!empty($errors)) {
            $message .= " However, some records failed: " . implode('; ', $errors);
            return redirect()->route('admin.attendance.index', ['date' => $attendanceDate]) // العودة إلى قائمة الحضور لنفس اليوم
                             ->with('error', $message);
        }

        return redirect()->route('admin.attendance.index', ['date' => $attendanceDate])
                         ->with('success', $message);
    }


    /**
     * Display the specified resource. (Not typically used for single attendance)
     * عرض سجل حضور واحد ليس شائعًا، عادةً ما يتم العرض في قائمة أو في ملف الطفل.
     */
    public function show(Attendance $attendance)
    {
        // يمكنك عرض صفحة تفاصيل إذا لزم الأمر، ولكن index أو ملف الطفل يكفي عادةً
        $attendance->load(['child.kindergartenClass', 'recordedByUser']);
        return view('web.admin.attendance.show', compact('attendance')); // تأكد من إنشاء هذا الـ view
    }

    /**
     * Show the form for editing the specified attendance record.
     * يعرض نموذج تعديل سجل حضور معين.
     *
     * @param  \App\Models\Attendance  $attendance // استخدام Route Model Binding
     */
    public function edit(Attendance $attendance)
    {
        // تحميل بيانات الطفل لتوضيح لمن يتم التعديل
        $attendance->load('child');

        // قائمة الحالات الممكنة للحضور
        $statuses = ['Present' => 'Present', 'Absent' => 'Absent', 'Late' => 'Late', 'Excused' => 'Excused'];

        // عرض واجهة تعديل سجل الحضور
        return view('web.admin.attendance.edit', compact('attendance', 'statuses')); // تأكد من إنشاء هذا الـ view
    }

    /**
     * Update the specified attendance record in storage.
     * يحدث سجل حضور معين في قاعدة البيانات.
     *
     * @param  \App\Http\Requests\Admin\UpdateAttendanceRequest  $request
     * @param  \App\Models\Attendance  $attendance
     */
    public function update(UpdateAttendanceRequest $request, Attendance $attendance) // استخدام Form Request
    {
        // الحصول على البيانات المتحقق منها
        $validated = $request->validated();

        // تحديث سجل الحضور (يمكن تحديث recorded_by_id أيضًا إذا لزم الأمر)
        $attendance->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'check_in_time' => $validated['check_in_time'] ?? null,
            'check_out_time' => $validated['check_out_time'] ?? null,
             // 'recorded_by_id' => Auth::id(), // تحديث هوية من قام بالتعديل
        ]);

        // إعادة التوجيه إلى قائمة الحضور لليوم الذي تم تعديله
        return redirect()->route('admin.attendance.index', ['date' => $attendance->attendance_date])
                         ->with('success', 'Attendance record updated successfully.');
    }

    /**
     * Remove the specified attendance record from storage.
     * يحذف سجل حضور معين.
     *
     * @param  \App\Models\Attendance  $attendance
     */
    public function destroy(Attendance $attendance)
    {
        try {
            // تخزين التاريخ قبل الحذف لإعادة التوجيه
            $attendanceDate = $attendance->attendance_date;
            // حذف سجل الحضور
            $attendance->delete();
            // إعادة التوجيه مع رسالة نجاح
            return redirect()->route('admin.attendance.index', ['date' => $attendanceDate])
                             ->with('success', 'Attendance record deleted successfully.');
        } catch (\Exception $e) {
            // في حال حدوث خطأ
            return redirect()->route('admin.attendance.index')
                             ->with('error', 'Failed to delete attendance record. Please try again.');
        }
    }
}