<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyMeal;
use App\Models\KindergartenClass; // للفلترة واختيار الفصل
use Illuminate\Http\Request;
use Carbon\Carbon; // للتعامل مع التواريخ
use App\Http\Requests\Admin\StoreDailyMealRequest; // استخدم Form Request
use App\Http\Requests\Admin\UpdateDailyMealRequest; // استخدم Form Request

class DailyMealController extends Controller
{
    /**
     * Display a listing of the daily meals.
     * عرض قائمة بالوجبات مع إمكانية الفلترة حسب التاريخ والفصل.
     */
    public function index(Request $request)
    {
        // --- الفلترة ---
        $query = DailyMeal::with('kindergartenClass'); // تحميل علاقة الفصل

        // 1. الفلترة حسب التاريخ
        $selectedDate = $request->query('date', now()->format('Y-m-d')); // الافتراضي تاريخ اليوم
        try {
            $filterDate = Carbon::createFromFormat('Y-m-d', $selectedDate)->format('Y-m-d');
             $query->whereDate('meal_date', $filterDate);
        } catch (\Exception $e) {
            $filterDate = now()->format('Y-m-d');
             $query->whereDate('meal_date', $filterDate);
        }

        // 2. الفلترة حسب الفصل الدراسي
        $selectedClassId = $request->query('class_id');
        if ($selectedClassId === 'general') { // خيار خاص للوجبات العامة
            $query->whereNull('class_id');
        } elseif ($selectedClassId) {
            $query->where('class_id', $selectedClassId);
        }
        // إذا لم يتم اختيار فصل، يعرض الكل (عام وخاص) لذلك اليوم

        // --- الترتيب والـ Pagination ---
        $meals = $query->orderBy('meal_type') // الترتيب حسب نوع الوجبة
                       ->paginate(10) // عرض 10 وجبات في الصفحة
                       ->withQueryString(); // للحفاظ على الفلاتر عند التنقل

        // --- بيانات إضافية للـ View ---
        $classes = KindergartenClass::orderBy('class_name')->pluck('class_name', 'class_id');

        // إرسال البيانات إلى الواجهة
        return view('web.admin.meals.index', compact('meals', 'classes', 'selectedDate', 'selectedClassId'));
    }

    /**
     * Show the form for creating a new daily meal.
     * يعرض نموذج إضافة وجبة جديدة.
     */
    public function create(Request $request)
    {
        // جلب تاريخ اليوم كقيمة افتراضية، يمكن تغييره
        $defaultDate = $request->query('date', now()->format('Y-m-d'));
        try {
            $defaultDate = Carbon::createFromFormat('Y-m-d', $defaultDate)->format('Y-m-d');
        } catch(\Exception $e) {
             $defaultDate = now()->format('Y-m-d');
        }

        // جلب قائمة الفصول للاختيار
        $classes = KindergartenClass::orderBy('class_name')->pluck('class_name', 'class_id');
        // قائمة بأنواع الوجبات
        $mealTypes = ['Breakfast' => 'فطور', 'Lunch' => 'غداء', 'Snack' => 'وجبة خفيفة'];

        // إرسال البيانات للواجهة
        return view('web.admin.meals.create', compact('classes', 'mealTypes', 'defaultDate'));
    }

    /**
     * Store a newly created daily meal in storage.
     * يخزن الوجبة الجديدة في قاعدة البيانات.
     *
     * @param  \App\Http\Requests\Admin\StoreDailyMealRequest  $request
     */
    public function store(StoreDailyMealRequest $request)
    {
        // الحصول على البيانات المتحقق منها
        $validated = $request->validated();

        // التحقق من عدم وجود وجبة مكررة (نفس اليوم، النوع، والفصل) - يمكن تحسينه في Form Request Rule
        $existing = DailyMeal::where('meal_date', $validated['meal_date'])
                             ->where('meal_type', $validated['meal_type'])
                             ->where('class_id', $validated['class_id'] ?? null)
                             ->exists();

        if ($existing) {
            return back()->withInput()->with('error', 'يوجد بالفعل وجبة من نفس النوع لهذا اليوم وهذا الفصل (أو عامة).');
        }

        // إنشاء الوجبة
        DailyMeal::create([
            'meal_date' => $validated['meal_date'],
            'meal_type' => $validated['meal_type'],
            'menu_description' => $validated['menu_description'],
            'class_id' => $validated['class_id'] ?? null, // استخدام null إذا لم يتم اختيار فصل
        ]);

        // إعادة التوجيه إلى قائمة الوجبات لنفس اليوم مع رسالة نجاح
        return redirect()->route('admin.meals.index', ['date' => $validated['meal_date']])
                         ->with('success', 'تمت إضافة الوجبة بنجاح.');
    }

    /**
     * Display the specified resource. (Optional)
     * عرض تفاصيل وجبة واحدة (قد لا تكون ضرورية).
     *
     * @param  \App\Models\DailyMeal  $dailyMeal // استخدام Route Model Binding
     */
    public function show(DailyMeal $dailyMeal)
    {
        $dailyMeal->load('kindergartenClass'); // تحميل علاقة الفصل
        return view('web.admin.meals.show', compact('dailyMeal'));
    }

    /**
     * Show the form for editing the specified daily meal.
     * يعرض نموذج تعديل وجبة موجودة.
     *
     * @param  \App\Models\DailyMeal  $meal
     */
    public function edit(DailyMeal $dailyMeal)
    {
        // جلب قائمة الفصول للاختيار
        $classes = KindergartenClass::orderBy('class_name')->pluck('class_name', 'class_id');
        // قائمة بأنواع الوجبات
        $mealTypes = ['Breakfast' => 'فطور', 'Lunch' => 'غداء', 'Snack' => 'وجبة خفيفة'];

        // إرسال بيانات الوجبة الحالية والبيانات الأخرى للواجهة
        return view('web.admin.meals.edit', compact('dailyMeal', 'classes', 'mealTypes'));
    }

    /**
     * Update the specified daily meal in storage.
     * يحدث بيانات الوجبة في قاعدة البيانات.
     *
     * @param  \App\Http\Requests\Admin\UpdateDailyMealRequest  $request
     * @param  \App\Models\DailyMeal  $meal
     */
    public function update(UpdateDailyMealRequest $request, DailyMeal $meal)
    {
        // الحصول على البيانات المتحقق منها
        $validated = $request->validated();

         // التحقق من عدم وجود وجبة مكررة (نفس اليوم، النوع، والفصل) مع تجاهل السجل الحالي
         $existing = DailyMeal::where('meal_date', $validated['meal_date'])
                             ->where('meal_type', $validated['meal_type'])
                             ->where('class_id', $validated['class_id'] ?? null)
                             ->where('meal_id', '!=', $meal->meal_id) // تجاهل الوجبة الحالية
                             ->exists();

        if ($existing) {
            return back()->withInput()->with('error', 'يوجد بالفعل وجبة أخرى من نفس النوع لهذا اليوم وهذا الفصل (أو عامة).');
        }

        // تحديث الوجبة
        $meal->update([
            'meal_date' => $validated['meal_date'],
            'meal_type' => $validated['meal_type'],
            'menu_description' => $validated['menu_description'],
            'class_id' => $validated['class_id'] ?? null,
        ]);

        // إعادة التوجيه إلى قائمة الوجبات لنفس اليوم مع رسالة نجاح
        return redirect()->route('admin.meals.index', ['date' => $meal->meal_date])
                         ->with('success', 'تم تحديث الوجبة بنجاح.');
    }

    /**
     * Remove the specified daily meal from storage.
     * يحذف الوجبة من قاعدة البيانات.
     *
     * @param  \App\Models\DailyMeal  $meal
     */
    public function destroy(DailyMeal $meal)
    {
        try {
            $mealDate = $meal->meal_date; // تخزين التاريخ قبل الحذف
            $meal->delete();
            return redirect()->route('admin.meals.index', ['date' => $mealDate])
                             ->with('success', 'تم حذف الوجبة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->route('admin.meals.index')
                             ->with('error', 'فشل حذف الوجبة. يرجى المحاولة مرة أخرى.');
        }
    }
}