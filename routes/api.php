<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChildController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\HealthRecordController;
use App\Http\Controllers\Api\DailyMealController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventRegistrationController;
use App\Http\Controllers\Api\EducationalResourceController;
use App\Http\Controllers\Api\ObservationController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RegisterController; // إذا قمت بإنشائه
use App\Http\Controllers\Api\ChildMealStatusController; // <-- استيراد المتحكم

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- Public Routes ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']); // تفعيل هذا إذا أردت التسجيل عبر API

// --- Authenticated Routes (Protected by Sanctum and Role Middleware) ---
// ->middleware(['auth:sanctum']) // يحمي بواسطة Sanctum
// ->middleware(['auth:sanctum', 'role:Parent']) // يحمي بواسطة Sanctum ودور ولي الأمر
Route::middleware(['auth:sanctum'])->group(function () {

    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) { // مسار Laravel الافتراضي لفحص المستخدم المسجل
        return $request->user()->load(['parentProfile']); // تحميل بيانات ولي الأمر المرتبطة
    });

    // Profile Management (Parent Specific)
    Route::get('/profile', [ProfileController::class, 'show'])->middleware('role:Parent');
    Route::put('/profile', [ProfileController::class, 'update'])->middleware('role:Parent');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->middleware('role:Parent');

    // Parent Specific Routes (Requires Parent Role)
    Route::middleware(['role:Parent'])->group(function () {
        // Children Data (Associated with the authenticated parent)
        Route::get('/children', [ChildController::class, 'index']); // يعرض أطفال ولي الأمر فقط
        Route::get('/children/{child}', [ChildController::class, 'show']); // يعرض طفل محدد لولي الأمر

        // Schedules (For the parent's children's classes)
        Route::get('/schedules', [ScheduleController::class, 'index']);

        // Health Records (For the parent's children)
        Route::get('/health-records', [HealthRecordController::class, 'index']);

        // Daily Meals
        Route::get('/meals', [DailyMealController::class, 'index']);

        // ---=== إضافة مسار حالات الوجبات ===---
        Route::get('/meal-statuses', [ChildMealStatusController::class, 'index']);

        // Announcements
        Route::get('/announcements', [AnnouncementController::class, 'index']);

        // Media (Relevant to the parent's children/classes)
        Route::get('/media', [MediaController::class, 'index']);

        // Events & Registration
        Route::get('/events', [EventController::class, 'index']);
        Route::get('/events/{event}', [EventController::class, 'show']);
        Route::post('/events/{event}/register', [EventRegistrationController::class, 'store']);
        Route::delete('/event-registrations/{registration}', [EventRegistrationController::class, 'destroy']); // طريقة أفضل لإلغاء التسجيل

        // Educational Resources
        Route::get('/educational-resources', [EducationalResourceController::class, 'index']);

        // Observations/Feedback from Parent
        Route::post('/observations', [ObservationController::class, 'store']);

        // Messages (Parent can view/send)
        Route::get('/messages', [MessageController::class, 'index']);
        Route::post('/messages', [MessageController::class, 'store']); // إرسال رسالة (للمدير/المشرف؟)
        Route::get('/messages/{message}', [MessageController::class, 'show']); // عرض رسالة محددة
    });

    // Note: Add routes accessible by Supervisor via API if needed here
    // Route::middleware(['role:Supervisor'])->group(function () { ... });

});