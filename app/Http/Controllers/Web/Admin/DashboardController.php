<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// استيراد النماذج اللازمة لجلب الإحصائيات
use App\Models\User;
use App\Models\Child;
use App\Models\KindergartenClass;
use App\Models\Event;
use App\Models\Announcement;
use App\Models\Attendance;
use Carbon\Carbon; // للعمل مع التواريخ (مثل إحصائيات اليوم)

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with key statistics and recent activity.
     */
    public function index()
    {
        // --- تنفيذ TODO: Fetch data for the dashboard ---

        // 1. الإحصائيات العددية الأساسية
        $stats = [
            'total_children' => Child::count(),
            'total_parents' => User::where('role', 'Parent')->count(),
            'total_classes' => KindergartenClass::count(),
            'total_staff' => User::whereIn('role', ['Admin', 'Supervisor'])->count(), // عدد المدراء والمشرفين
            'upcoming_events' => Event::where('event_date', '>=', now())->count(), // عدد الفعاليات القادمة
        ];

        // 2. إحصائيات الحضور لليوم الحالي
        $today = now()->toDateString();
        $attendanceToday = [
            'present' => Attendance::whereDate('attendance_date', $today)->where('status', 'Present')->count(),
            'absent' => Attendance::whereDate('attendance_date', $today)->where('status', 'Absent')->count(),
            'late' => Attendance::whereDate('attendance_date', $today)->where('status', 'Late')->count(),
            'total_recorded' => Attendance::whereDate('attendance_date', $today)->count(), // إجمالي المسجلين اليوم
            'total_enrolled' => $stats['total_children'], // إجمالي الأطفال المسجلين للمقارنة
        ];
        // حساب نسبة الحضور (مع تجنب القسمة على صفر)
        $attendanceToday['present_percentage'] = $attendanceToday['total_recorded'] > 0
            ? round(($attendanceToday['present'] / $attendanceToday['total_recorded']) * 100)
            : 0;

        // 3. أحدث الأنشطة (أمثلة)
        $recentActivities = [
            'latest_children' => Child::with('kindergartenClass')->latest()->take(5)->get(), // أحدث 5 أطفال تم إضافتهم
            'latest_announcements' => Announcement::with('author.user')->latest('publish_date')->take(5)->get(), // أحدث 5 إعلانات
            'upcoming_events_list' => Event::where('event_date', '>=', now())->orderBy('event_date', 'asc')->take(5)->get(), // أقرب 5 فعاليات قادمة
        ];

        // إرسال جميع البيانات المجمعة إلى الواجهة
        return view('web.admin.dashboard', compact('stats', 'attendanceToday', 'recentActivities'));
    }
}