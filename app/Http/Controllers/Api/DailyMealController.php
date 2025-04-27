<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyMeal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- استيراد Auth
use App\Http\Resources\DailyMealResource;
use Carbon\Carbon; // <-- استيراد Carbon للتعامل مع التواريخ

class DailyMealController extends Controller
{
    /**
     * Display daily meals relevant to the authenticated parent.
     * (Filters by date and parent's children's classes)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // --- التحقق من صحة التاريخ (تنفيذ TODO 1) ---
        $validated = $request->validate([
            // 'sometimes' يعني أن الحقل اختياري
            // 'date_format:Y-m-d' يتأكد من أن التنسيق هو سنة-شهر-يوم
            'date' => 'sometimes|date_format:Y-m-d',
        ]);

        // تحديد التاريخ المطلوب للاستعلام
        // إذا تم توفير تاريخ صالح، استخدمه، وإلا استخدم تاريخ اليوم
        $targetDate = isset($validated['date'])
                      ? Carbon::createFromFormat('Y-m-d', $validated['date'])->toDateString()
                      : now()->toDateString();

        // --- فلترة حسب فصول أطفال ولي الأمر (تنفيذ TODO 2) ---
        $user = Auth::user();
        $parent = $user->parentProfile; // افتراض وجود علاقة parentProfile في نموذج User

        // إذا لم يكن المستخدم ولي أمر (نظرياً لن يحدث بسبب الـ middleware، لكنه فحص إضافي)
        if (!$parent) {
             // يمكنك إرجاع مجموعة فارغة أو خطأ حسب ما تفضل
             // return DailyMealResource::collection([]);
             return response()->json(['error' => 'Parent profile not found for the authenticated user.'], 403);
        }

        // الحصول على معرفات الفصول الفريدة لأطفال ولي الأمر (تجاهل القيم الفارغة)
        $childClassIds = $parent->children() // الحصول على علاقة الأطفال
                                ->whereNotNull('class_id') // تجاهل الأطفال غير المسجلين في فصل
                                ->pluck('class_id') // الحصول على عمود class_id فقط
                                ->unique() // الحصول على القيم الفريدة فقط
                                ->filter() // إزالة أي قيم null متبقية (احتياطي)
                                ->toArray(); // تحويلها إلى مصفوفة

        // --- بناء الاستعلام ---
        $query = DailyMeal::query()
                 ->with('kindergartenClass') // تحميل علاقة الفصل لتحسين الأداء في الريسورس
                 ->whereDate('meal_date', $targetDate); // الفلترة حسب التاريخ المستهدف

        // إضافة شرط الفلترة حسب الصلة بولي الأمر:
        // الوجبة إما عامة (class_id is NULL) أو مخصصة لأحد فصول أطفال ولي الأمر
        $query->where(function ($subQuery) use ($childClassIds) {
            $subQuery->whereNull('class_id') // الوجبات العامة
                     ->orWhereIn('class_id', $childClassIds); // أو الوجبات المخصصة لفصولهم
        });

        // --- الحصول على النتائج والترتيب ---
        $meals = $query->orderBy('meal_type') // ترتيب حسب نوع الوجبة (فطور، غداء، ...)
                       ->get();

        // --- إرجاع النتائج باستخدام الريسورس ---
        return DailyMealResource::collection($meals);
    }

    // show, store, update, destroy not needed for parents
}