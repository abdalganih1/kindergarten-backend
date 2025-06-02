<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChildMealStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ChildMealStatusResource;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Models\ParentModel; // يمكنك استيراده إذا أردت التأكد من النوع

class ChildMealStatusController extends Controller
{
    /**
     * Display a listing of the meal statuses for the authenticated parent's children.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $parent = $user ? $user->parentProfile : null; // افتراض وجود علاقة parentProfile

        if (!$parent) {
            return response()->json(['error' => 'Parent profile not found.'], 403);
        }

        // التحقق من صحة المدخلات
        $validated = $request->validate([
            'date' => 'sometimes|date_format:Y-m-d',
            'child_id' => [
                'sometimes',
                'integer',
                // ---=== تعديل هنا لتحديد الجدول ===---
                Rule::exists('children', 'child_id')->where(function ($query) use ($parent) {
                    $query->whereIn('children.child_id', $parent->children()->pluck('children.child_id')); // تحديد children.child_id
                }),
                // ---=== نهاية التعديل ===---
            ],
            'per_page' => 'sometimes|integer|min:1|max:50'
        ]);

        $targetDate = $validated['date'] ?? now()->format('Y-m-d');
        $perPage = $validated['per_page'] ?? 15;

        // جلب معرفات أطفال ولي الأمر
        // ---=== تعديل هنا لتحديد الجدول ===---
        $childIds = $parent->children()->pluck('children.child_id'); // تحديد children.child_id
        // ---=== نهاية التعديل ===---

        if ($childIds->isEmpty()) {
            // استخدام collection فارغة مع paginate لتجنب الأخطاء والحفاظ على بنية الرد
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, $perPage, 1,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            );
            return ChildMealStatusResource::collection($emptyPaginator);
        }

        $query = ChildMealStatus::with(['dailyMeal.kindergartenClass', 'child'])
                 ->whereHas('dailyMeal', function ($mealQuery) use ($targetDate) {
                     $mealQuery->whereDate('meal_date', $targetDate);
                 });

        if (isset($validated['child_id'])) {
            $query->where('child_id', $validated['child_id']);
        } else {
             $query->whereIn('child_id', $childIds->all()); // استخدام all() لتحويل Collection إلى array
        }

        $statuses = $query->join('daily_meals', 'child_meal_statuses.meal_id', '=', 'daily_meals.meal_id')
                          ->orderBy('daily_meals.meal_type')
                          ->select('child_meal_statuses.*')
                          ->paginate($perPage);

        return ChildMealStatusResource::collection($statuses);
    }
}