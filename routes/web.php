<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // إضافة Auth هنا

// Import Admin Controllers
use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Web\Admin\UserController as AdminUserController;
use App\Http\Controllers\Web\Admin\ChildController as AdminChildController;
use App\Http\Controllers\Web\Admin\KindergartenClassController as AdminClassController;
use App\Http\Controllers\Web\Admin\WeeklyScheduleController as AdminScheduleController;
use App\Http\Controllers\Web\Admin\DailyMealController as AdminMealController;
use App\Http\Controllers\Web\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Web\Admin\EventController as AdminEventController;
use App\Http\Controllers\Web\Admin\EducationalResourceController as AdminResourceController;
use App\Http\Controllers\Web\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Web\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Web\Admin\ObservationController as AdminObservationController;
use App\Http\Controllers\Web\Admin\MessageController as AdminMessageController;

// Import Supervisor Controllers
use App\Http\Controllers\Web\Supervisor\DashboardController as SupervisorDashboardController;
use App\Http\Controllers\Web\Supervisor\HealthRecordController as SupervisorHealthRecordController;
use App\Http\Controllers\Web\Supervisor\MediaController as SupervisorMediaController;
use App\Http\Controllers\Web\Supervisor\AttendanceController as SupervisorAttendanceController;
use App\Http\Controllers\Web\Supervisor\MessageController as SupervisorMessageController;
use App\Http\Controllers\Web\Supervisor\ChildController as SupervisorChildController;
use App\Http\Controllers\Web\Supervisor\ScheduleController as SupervisorScheduleController;
use App\Http\Controllers\Web\Supervisor\ChildMealStatusController as SupervisorMealStatusController; // <-- استيراد المتحكم الجديد

// CRUD (except edit/update)
use App\Http\Controllers\Web\Supervisor\EventController as SupervisorEventController; // عرض
use App\Http\Controllers\Web\Supervisor\EducationalResourceController as SupervisorResourceController; // CRUD


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->role == 'Admin') { return redirect()->route('admin.dashboard'); } // استخدم اسم المسار
        if (Auth::user()->role == 'Supervisor') { return redirect()->route('supervisor.dashboard'); } // استخدم اسم المسار
        return redirect('/home'); // إعادة توجيه افتراضية للمستخدمين الآخرين (مثل Parent إذا لم يكن لديهم dashboard)
    }
    return view('welcome');
});

// --- Web Authentication Routes ---
Auth::routes(['register' => true]); // أبقِ هذا إذا كنت تستخدم laravel/ui


// --- Admin Routes ---
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', AdminUserController::class);
    Route::resource('children', AdminChildController::class);
    Route::resource('classes', AdminClassController::class)->parameters(['classes' => 'kindergartenClass']);
    Route::resource('schedules', AdminScheduleController::class)->parameters(['schedules' => 'weeklySchedule']);
    Route::resource('meals', AdminMealController::class)->parameters(['meals' => 'dailyMeal']);
    Route::resource('announcements', AdminAnnouncementController::class);
    Route::resource('events', AdminEventController::class);
    Route::resource('resources', AdminResourceController::class)->parameters(['resources' => 'educationalResource']);
    Route::resource('media', AdminMediaController::class)->parameters(['media' => 'medium']); // تحديد اسم البارامتر لـ media
    Route::resource('observations', AdminObservationController::class)->only(['index', 'show', 'destroy']);
    Route::resource('messages', AdminMessageController::class)->only(['index', 'show', 'create','store','destroy']);

    // --- مسارات الحضور والغياب للمدير ---
    Route::get('/attendance/create-batch', [AdminAttendanceController::class, 'createBatch'])->name('attendance.createBatchForm'); // <-- تعريف المسار المخصص هنا
    Route::resource('attendance', AdminAttendanceController::class); // يفضل وضع resource بعد المسارات المخصصة للمورد نفسه لتجنب التعارض
    // ------------------------------------

});


// --- Supervisor Routes ---
// --- Supervisor Routes (Updated Permissions) ---
Route::middleware(['auth', 'role:Supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/dashboard', [SupervisorDashboardController::class, 'index'])->name('dashboard');

    // --- CRUD Permissions ---
    Route::resource('schedules', SupervisorScheduleController::class)->parameters(['schedules' => 'weeklySchedule']); // إدارة النشاطات (الجدول الأسبوعي)
    Route::resource('health-records', SupervisorHealthRecordController::class)->parameters(['health_records' => 'healthRecord']); // إدارة صحة الطفل
    Route::resource('resources', SupervisorResourceController::class)->parameters(['resources' => 'educationalResource']); // إدارة المواد التعليمية
    Route::resource('media', SupervisorMediaController::class)->parameters(['media' => 'medium']); // إدارة الصور والفيديو
    Route::resource('messages', SupervisorMessageController::class)->except(['edit', 'update']); // إدارة الرسائل

    // --- Attendance (Needs Batch Form Route) ---
    Route::get('/attendance/create-batch', [SupervisorAttendanceController::class, 'createBatch'])->name('attendance.createBatchForm');
    Route::resource('attendance', SupervisorAttendanceController::class); // إدارة الحضور

    // --- Read-only Permissions ---
    Route::resource('events', SupervisorEventController::class)->only(['index', 'show']); // عرض الفعاليات فقط
    Route::resource('children', SupervisorChildController::class)->only(['index', 'show']); // عرض ملف الطفل فقط

      // ---=== إضافة مسارات حالات الوجبات ===---
      Route::get('/meal-statuses/create', [SupervisorMealStatusController::class, 'create'])->name('meal_statuses.create'); // مسار عرض النموذج
      Route::post('/meal-statuses', [SupervisorMealStatusController::class, 'store'])->name('meal_statuses.store');       // مسار تخزين البيانات
      
});

// هذا السطر يكرر تعريف مسارات المصادقة، أزل أحدهما
// Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home')->middleware('auth'); // إضافة middleware للتحقق من تسجيل الدخول للوصول لـ home