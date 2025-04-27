<?php

namespace App\Http\Controllers\Web\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Child;
use App\Models\KindergartenClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\Rule; // لاستخدام Rule::in
// استخدام نفس Form Requests الخاصة بالمدير إذا كانت الصلاحيات متطابقة، أو إنشاء جديدة للمشرف
use App\Http\Requests\Admin\StoreAttendanceRequest as SupervisorStoreAttendanceRequest; // إعادة تسمية عند الاستخدام
use App\Http\Requests\Admin\UpdateAttendanceRequest as SupervisorUpdateAttendanceRequest; // إعادة تسمية عند الاستخدام

class AttendanceController extends Controller
{
    /**
     * Get the classes supervised by the current user.
     * (Helper function - adapt based on your actual implementation)
     * @return \Illuminate\Support\Collection
     */
    private function getSupervisorClassIds()
    {
        $user = Auth::user();
        // TODO: Implement supervisor class scoping
        // Example 1: Assuming a relationship 'supervisorClasses' on User model
        // if ($user->can('viewAny', Attendance::class)) { // Check policy if exists
        //     return $user->supervisorClasses()->pluck('class_id');
        // }
        // Example 2: Simple - Assume supervisor can manage all classes (adjust as needed)
         return KindergartenClass::pluck('class_id');

        // Example 3: Get from a specific profile if exists
        // return $user->supervisorProfile->classes()->pluck('class_id');

        // return collect(); // Return empty collection if no classes assigned
    }


    /**
     * Display a listing of the attendance records for supervised classes.
     */
    public function index(Request $request)
    {
        $supervisorClassIds = $this->getSupervisorClassIds();

        // إذا لم يكن المشرف مسؤولاً عن أي فصول، أرجع صفحة فارغة أو رسالة خطأ
        if ($supervisorClassIds->isEmpty()) {
             return view('web.supervisor.attendance.index', [
                'attendances' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15), // مقسم صفحات فارغ
                'supervisedClasses' => collect(),
                'selectedDate' => now()->format('Y-m-d'),
                'selectedClassId' => null,
                'searchTerm' => null,
                'noClassesAssigned' => true // متغير إضافي للإشارة لعدم وجود فصول
            ]);
        }


        $query = Attendance::with(['child.kindergartenClass', 'recordedByUser'])
                         ->select('attendances.*')
                         ->join('children', 'attendances.child_id', '=', 'children.child_id')
                         ->whereIn('children.class_id', $supervisorClassIds); // *** فلترة حسب فصول المشرف ***

        // --- الفلترة والبحث (مشابهة للمدير) ---
        // 1. الفلترة حسب التاريخ
        $selectedDate = $request->query('date', now()->format('Y-m-d'));
        try {
            $filterDate = Carbon::createFromFormat('Y-m-d', $selectedDate)->format('Y-m-d');
             $query->whereDate('attendances.attendance_date', $filterDate);
        } catch (\Exception $e) {
            $filterDate = now()->format('Y-m-d');
             $query->whereDate('attendances.attendance_date', $filterDate);
        }

        // 2. الفلترة حسب فصل دراسي (من ضمن فصول المشرف)
        $selectedClassId = $request->query('class_id');
        if ($selectedClassId && $supervisorClassIds->contains($selectedClassId)) { // تأكد من أن المشرف يمكنه رؤية هذا الفصل
             $query->where('children.class_id', $selectedClassId);
        } elseif ($selectedClassId) {
            // إذا حاول المشرف اختيار فصل ليس له صلاحية عليه، أرجع فارغًا أو تجاهل الفلتر
             $query->whereRaw('1 = 0'); // لا يوجد نتائج
        }


        // 3. البحث عن اسم الطفل
        $searchTerm = $request->query('search');
        if ($searchTerm) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('children.first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('children.last_name', 'like', "%{$searchTerm}%");
            });
        }

        // --- الترتيب والـ Pagination ---
        $attendances = $query->latest('attendances.created_at')
                             ->paginate(20)
                             ->withQueryString();

        // --- بيانات إضافية للـ View (فصول المشرف فقط) ---
        $supervisedClasses = KindergartenClass::whereIn('class_id', $supervisorClassIds)
                                         ->orderBy('class_name')
                                         ->pluck('class_name', 'class_id');

        return view('web.supervisor.attendance.index', compact(
            'attendances',
            'supervisedClasses',
            'selectedDate',
            'selectedClassId',
            'searchTerm'
        ));
    }

     /**
     * Show the form for recording attendance for a specific supervised class on a specific date.
     * عرض نموذج تسجيل الحضور الجماعي لفصل يشرف عليه المستخدم.
     */
    public function createBatch(Request $request)
    {
         $supervisorClassIds = $this->getSupervisorClassIds();

         $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'class_id' => [
                'required',
                'integer',
                Rule::in($supervisorClassIds) // *** التحقق من أن الفصل من ضمن فصول المشرف ***
            ],
        ]);

        $attendanceDate = $validated['date'];
        $classId = $validated['class_id'];

        $classInfo = KindergartenClass::find($classId);
        // لا حاجة للتحقق من وجود الفصل مرة أخرى بسبب Rule::in

        // جلب أطفال الفصل مع حالة حضورهم لهذا اليوم (إن وجدت)
        $children = Child::where('class_id', $classId)
                         ->with(['attendances' => function ($query) use ($attendanceDate) {
                             $query->whereDate('attendance_date', $attendanceDate);
                         }])
                         ->orderBy('first_name')
                         ->get();

        $statuses = ['Present' => 'Present', 'Absent' => 'Absent', 'Late' => 'Late', 'Excused' => 'Excused'];

        // عرض واجهة تسجيل الحضور الجماعي
        return view('web.supervisor.attendance.create_batch', compact('children', 'statuses', 'attendanceDate', 'classInfo'));
    }


    /**
     * Store newly created attendance records (handles batch saving for supervised classes).
     * يخزن سجلات الحضور الجديدة.
     *
     * @param  SupervisorStoreAttendanceRequest $request // استخدام Form Request (أو Request العادي مع تحقق هنا)
     */
    public function store(Request $request) // استخدام Request العادي مؤقتًا
    {
        // التحقق الأساسي
        $validatedDate = $request->validate([
            'attendance_date' => 'required|date_format:Y-m-d',
            'attendance' => 'required|array',
            'attendance.*.child_id' => 'required|integer|exists:children,child_id',
            'attendance.*.status' => ['required', 'string', Rule::in(['Present', 'Absent', 'Late', 'Excused'])],
            'attendance.*.notes' => 'nullable|string|max:1000',
            'attendance.*.check_in_time' => 'nullable|date_format:H:i,H:i:s',
            'attendance.*.check_out_time' => 'nullable|date_format:H:i,H:i:s',
        ]);

        $attendanceDate = $validatedDate['attendance_date'];
        $attendanceData = $validatedDate['attendance'];
        $recordedById = Auth::id();
        $supervisorClassIds = $this->getSupervisorClassIds(); // الحصول على فصول المشرف
        $errors = [];
        $successCount = 0;
        $unauthorizedCount = 0;

        foreach ($attendanceData as $childAttendance) {
            try {
                // *** التحقق من الصلاحية: هل الطفل ينتمي لأحد فصول المشرف؟ ***
                $child = Child::find($childAttendance['child_id']);
                if (!$child || !$supervisorClassIds->contains($child->class_id)) {
                     $unauthorizedCount++;
                    $errors[] = "Unauthorized to record attendance for child ID {$childAttendance['child_id']}.";
                    continue; // تخطي هذا الطفل
                }

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
                $childName = $child->full_name ?? $childAttendance['child_id'];
                $errors[] = "Failed to save attendance for child {$childName}: " . $e->getMessage();
                 \Log::error("Supervisor Attendance save error for child {$childAttendance['child_id']} on {$attendanceDate}: " . $e->getMessage());
            }
        }

        // رسالة النجاح أو الخطأ
        $message = $successCount . " attendance records saved successfully for " . $attendanceDate . ".";
        if ($unauthorizedCount > 0) {
             $message .= " ({$unauthorizedCount} records skipped due to unauthorized access).";
        }
        if (!empty($errors)) {
            $message .= " Errors occurred: " . implode('; ', $errors);
            return redirect()->route('supervisor.attendance.index', ['date' => $attendanceDate])
                             ->with('warning', $message); // استخدام warning
        }

        return redirect()->route('supervisor.attendance.index', ['date' => $attendanceDate])
                         ->with('success', $message);
    }

    /**
     * Show the form for editing the specified attendance record (if supervisor has access).
     * عرض نموذج تعديل سجل حضور لطفل في فصل يشرف عليه المستخدم.
     *
     * @param  \App\Models\Attendance  $attendance
     */
    public function edit(Attendance $attendance)
    {
        // *** التحقق من الصلاحية: هل السجل لطفل في فصل يشرف عليه؟ ***
        $supervisorClassIds = $this->getSupervisorClassIds();
        if (!$supervisorClassIds->contains($attendance->child->class_id)) {
            abort(403, 'Unauthorized access to this attendance record.');
        }

        $attendance->load('child');
        $statuses = ['Present' => 'Present', 'Absent' => 'Absent', 'Late' => 'Late', 'Excused' => 'Excused'];

        return view('web.supervisor.attendance.edit', compact('attendance', 'statuses'));
    }

    /**
     * Update the specified attendance record in storage (if supervisor has access).
     * تحديث سجل حضور طفل في فصل يشرف عليه المستخدم.
     *
     * @param  SupervisorUpdateAttendanceRequest  $request // استخدام Form Request
     * @param  \App\Models\Attendance  $attendance
     */
    public function update(SupervisorUpdateAttendanceRequest $request, Attendance $attendance)
    {
         // *** التحقق من الصلاحية (يمكن وضعه في Form Request authorize) ***
        $supervisorClassIds = $this->getSupervisorClassIds();
        if (!$supervisorClassIds->contains($attendance->child->class_id)) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validated();

        // تحديث سجل الحضور
        $attendance->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'check_in_time' => $validated['check_in_time'] ?? null,
            'check_out_time' => $validated['check_out_time'] ?? null,
             // 'recorded_by_id' => Auth::id(), // تحديث هوية من قام بالتعديل
        ]);

        return redirect()->route('supervisor.attendance.index', ['date' => $attendance->attendance_date])
                         ->with('success', 'Attendance record updated successfully.');
    }

    /**
     * Remove the specified attendance record from storage (if supervisor has access).
     * حذف سجل حضور طفل في فصل يشرف عليه المستخدم.
     *
     * @param  \App\Models\Attendance  $attendance
     */
    public function destroy(Attendance $attendance)
    {
        // *** التحقق من الصلاحية ***
        $supervisorClassIds = $this->getSupervisorClassIds();
         if (!$supervisorClassIds->contains($attendance->child->class_id)) {
             abort(403, 'Unauthorized action.');
        }

        try {
            $attendanceDate = $attendance->attendance_date;
            $attendance->delete();
            return redirect()->route('supervisor.attendance.index', ['date' => $attendanceDate])
                             ->with('success', 'Attendance record deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('supervisor.attendance.index')
                             ->with('error', 'Failed to delete attendance record.');
        }
    }

    // الدوال create و show الفردية قد لا تكون ضرورية للمشرف
}