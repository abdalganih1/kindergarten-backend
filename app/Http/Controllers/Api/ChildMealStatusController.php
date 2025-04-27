<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChildMealStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ChildMealStatusResource; // استخدام الريسورس
use Carbon\Carbon;
use Illuminate\Validation\Rule;
class ChildMealStatusController extends Controller
{
    /**
     * Display a listing of the meal statuses for the authenticated parent's children.
     * عرض قائمة بحالات الوجبات لأطفال ولي الأمر مع الفلترة حسب التاريخ والطفل.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $parent = $user->parentProfile;

        if (!$parent) {
            return response()->json(['error' => 'Parent profile not found.'], 403);
        }

        // التحقق من صحة المدخلات
        $validated = $request->validate([
            'date' => 'sometimes|date_format:Y-m-d', // تاريخ اختياري
            'child_id' => [ // طفل اختياري (يجب أن يكون تابعًا لولي الأمر)
                'sometimes',
                'integer',
                 Rule::exists('children', 'child_id')->where(function ($query) use ($parent) {
                    $query->whereIn('child_id', $parent->children()->pluck('child_id'));
                }),
            ],
            'per_page' => 'sometimes|integer|min:1|max:50' // عدد العناصر لكل صفحة
        ]);

        // تحديد التاريخ المستهدف
        $targetDate = $validated['date'] ?? now()->format('Y-m-d');
        $perPage = $validated['per_page'] ?? 15;

        // جلب معرفات أطفال ولي الأمر
        $childIds = $parent->children()->pluck('child_id');
        if ($childIds->isEmpty()) {
            return ChildMealStatusResource::collection(collect()); // إرجاع مجموعة فارغة
        }

        // بناء الاستعلام
        $query = ChildMealStatus::with(['dailyMeal.kindergartenClass', 'child']) // تحميل العلاقات اللازمة للريسورس
                 // فلترة حسب التاريخ من خلال العلاقة مع dailyMeal
                 ->whereHas('dailyMeal', function ($mealQuery) use ($targetDate) {
                     $mealQuery->whereDate('meal_date', $targetDate);
                 });

        // فلترة حسب طفل معين إذا تم تحديده
        if (isset($validated['child_id'])) {
            $query->where('child_id', $validated['child_id']);
        } else {
             // إذا لم يتم تحديد طفل، جلب حالات جميع أطفال ولي الأمر
             $query->whereIn('child_id', $childIds);
        }

        // الترتيب والـ Pagination
        $statuses = $query->join('daily_meals', 'child_meal_statuses.meal_id', '=', 'daily_meals.meal_id') // للانضمام للترتيب حسب نوع الوجبة
                          ->orderBy('daily_meals.meal_type') // الترتيب حسب نوع الوجبة (فطور، غداء..)
                          ->select('child_meal_statuses.*') // تحديد أعمدة الجدول الأساسي
                          ->paginate($perPage);

        return ChildMealStatusResource::collection($statuses);
    }

    // لا نحتاج store, update, destroy لولي الأمر هنا
    // قد نحتاج show إذا أردنا عرض تفاصيل حالة معينة، لكن index تكفي عادةً
}