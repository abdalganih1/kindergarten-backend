<?php

namespace App\Http\Controllers\Web\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\ChildMealStatus;
use App\Models\DailyMeal;
use App\Models\Child;
use App\Models\KindergartenClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // لاستخدام Transaction
use App\Http\Requests\Supervisor\StoreChildMealStatusRequest; // استخدام Form Request
use Carbon\Carbon;
use Illuminate\Validation\Rule;
class ChildMealStatusController extends Controller
{
    /**
     * Get the classes supervised by the current user.
     */
    private function getSupervisorClassIds()
    {
        $user = Auth::user();
        // TODO: Implement supervisor class scoping correctly
        return KindergartenClass::pluck('class_id'); // Example
    }

    /**
     * Show the form for recording meal statuses for a specific class and date.
     * عرض نموذج إدخال حالات الوجبات لفصل وتاريخ محددين.
     */
    public function create(Request $request)
    {
        $supervisorClassIds = $this->getSupervisorClassIds();
        if ($supervisorClassIds->isEmpty()) {
             return redirect()->route('supervisor.dashboard')->with('warning', 'لم يتم تعيين فصول لك.'); // توجيه للداشبورد
        }

        // التحقق من التاريخ والفصل المطلوبين
        $validated = $request->validate([
            'date' => 'sometimes|date_format:Y-m-d',
            'class_id' => ['sometimes', 'integer', Rule::in($supervisorClassIds)], // يجب أن يكون الفصل ضمن صلاحيات المشرف
        ]);

        $selectedDate = $validated['date'] ?? now()->format('Y-m-d');
        $selectedClassId = $validated['class_id'] ?? null; // قد لا يتم اختيار فصل مبدئيًا

        // جلب الفصول التي يشرف عليها للاختيار منها
        $supervisedClasses = KindergartenClass::whereIn('class_id', $supervisorClassIds)
                                         ->orderBy('class_name')
                                         ->pluck('class_name', 'class_id');

        $childrenWithMeals = collect(); // مجموعة فارغة مبدئيًا
        $mealsForDay = collect();

        // جلب الأطفال والوجبات فقط إذا تم اختيار فصل وتاريخ
        if ($selectedClassId) {
            // جلب وجبات اليوم لهذا الفصل (والوجبات العامة)
            $mealsForDay = DailyMeal::where('meal_date', $selectedDate)
                                     ->where(function ($q) use ($selectedClassId) {
                                         $q->where('class_id', $selectedClassId)
                                           ->orWhereNull('class_id'); // جلب الوجبات العامة أيضًا
                                     })
                                     ->orderBy('meal_type') // ترتيب حسب نوع الوجبة
                                     ->get();

            // جلب أطفال الفصل مع تحميل حالات الوجبات المسجلة لهم لهذا اليوم وهذه الوجبات
            if (!$mealsForDay->isEmpty()) {
                $mealIds = $mealsForDay->pluck('meal_id');
                $childrenWithMeals = Child::where('class_id', $selectedClassId)
                                         ->with(['mealStatuses' => function ($query) use ($mealIds) {
                                             $query->whereIn('meal_id', $mealIds); // تحميل الحالات للوجبات المحددة فقط
                                         }])
                                         ->orderBy('first_name')
                                         ->get();
            } else {
                // إذا لم يكن هناك وجبات لليوم، لا داعي لجلب الأطفال لهذا النموذج تحديدًا
                // يمكنك تغيير هذا السلوك إذا أردت عرض الأطفال دائمًا
            }
        }

        // قائمة حالات التناول
        $consumptionStatuses = [
            'EatenWell' => 'أكل جيدًا', 'EatenSome' => 'أكل البعض',
            'EatenLittle' => 'أكل القليل', 'NotEaten' => 'لم يأكل',
            'Refused' => 'رفض الأكل', 'Absent' => 'غائب'
        ];

        return view('web.supervisor.meal_statuses.create', compact(
            'supervisedClasses',
            'selectedClassId',
            'selectedDate',
            'childrenWithMeals',
            'mealsForDay',
            'consumptionStatuses'
        ));
    }


    /**
     * Store the meal statuses for multiple children and meals.
     * تخزين حالات الوجبات لمجموعة من الأطفال والوجبات.
     *
     * @param  StoreChildMealStatusRequest $request
     */
    public function store(StoreChildMealStatusRequest $request)
    {
        $validated = $request->validated();
        $supervisorId = Auth::id();
        $supervisorClassIds = $this->getSupervisorClassIds();
        $statusData = $validated['statuses']; // مصفوفة الحالات

        $successCount = 0;
        $errorCount = 0;

        DB::beginTransaction();
        try {
            foreach ($statusData as $childId => $meals) {
                // التحقق من أن الطفل ينتمي لفصل يشرف عليه المستخدم
                $child = Child::find($childId);
                if (!$child || !$supervisorClassIds->contains($child->class_id)) {
                    $errorCount++; // زيادة عداد الأخطاء وتخطي الطفل
                    \Log::warning("Supervisor {$supervisorId} attempted to record meal status for unauthorized child {$childId}.");
                    continue;
                }

                foreach ($meals as $mealId => $statusInfo) {
                    // التحقق من أن الوجبة موجودة (ويمكن إضافة تحقق من تاريخها إذا لزم الأمر)
                    $mealExists = DailyMeal::where('meal_id', $mealId)->exists();
                    if (!$mealExists) {
                        $errorCount++;
                         \Log::warning("Supervisor {$supervisorId} attempted to record status for non-existent meal {$mealId} for child {$childId}.");
                        continue;
                    }

                    // تحديث أو إنشاء سجل الحالة
                    ChildMealStatus::updateOrCreate(
                        [
                            'child_id' => $childId,
                            'meal_id' => $mealId,
                        ],
                        [
                            'consumption_status' => $statusInfo['status'],
                            'notes' => $statusInfo['notes'] ?? null,
                            'recorded_by_id' => $supervisorId,
                        ]
                    );
                    $successCount++;
                }
            }

            DB::commit();

            $message = "تم حفظ {$successCount} حالة وجبة بنجاح.";
            if ($errorCount > 0) {
                $message .= " فشلت {$errorCount} عملية حفظ بسبب عدم صلاحية أو بيانات خاطئة.";
                return redirect()->route('supervisor.meal_statuses.create', ['date' => $validated['date'], 'class_id' => $validated['class_id']])
                                 ->with('warning', $message);
            }

            return redirect()->route('supervisor.meal_statuses.create', ['date' => $validated['date'], 'class_id' => $validated['class_id']])
                             ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error storing child meal statuses by supervisor {$supervisorId}: " . $e->getMessage());
            return back()->withInput()->with('error', 'حدث خطأ غير متوقع أثناء حفظ الحالات. يرجى المحاولة مرة أخرى.');
        }
    }

    // لا حاجة لـ index, show, edit, update, destroy للمشرف في هذا المتحكم عادةً
    // يمكن عرض السجلات في صفحات أخرى (مثل ملف الطفل) أو متحكم تقارير
}