<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\KindergartenClass;
use App\Models\Child; // للتحقق قبل الحذف
use Illuminate\Http\Request;
use App\Http\Requests\Admin\StoreKindergartenClassRequest; // يجب إنشاؤه
use App\Http\Requests\Admin\UpdateKindergartenClassRequest; // يجب إنشاؤه

class KindergartenClassController extends Controller
{
    /**
     * Display a listing of the kindergarten classes.
     * عرض قائمة بالفصول مع عدد الأطفال في كل فصل.
     */
    public function index()
    {
        // جلب الفصول مع حساب عدد الأطفال المرتبطين بكل فصل
        $classes = KindergartenClass::withCount('children') // يحسب children_count
                                    ->orderBy('min_age') // الترتيب حسب العمر الأدنى
                                    ->orderBy('class_name')
                                    ->paginate(10); // عدد الفصول في الصفحة

        return view('web.admin.classes.index', compact('classes'));
    }

    /**
     * Show the form for creating a new kindergarten class.
     * عرض نموذج إضافة فصل جديد.
     */
    public function create()
    {
        return view('web.admin.classes.create');
    }

    /**
     * Store a newly created kindergarten class in storage.
     * تخزين الفصل الجديد.
     *
     * @param  \App\Http\Requests\Admin\StoreKindergartenClassRequest  $request
     */
    public function store(StoreKindergartenClassRequest $request)
    {
        // الحصول على البيانات المتحقق منها
        $validated = $request->validated();

        // إنشاء الفصل
        KindergartenClass::create($validated);

        // إعادة التوجيه مع رسالة نجاح
        return redirect()->route('admin.classes.index')
                         ->with('success', 'تم إنشاء الفصل بنجاح.');
    }

    /**
     * Display the specified kindergarten class and its children. (Optional)
     * عرض تفاصيل فصل معين وقائمة الأطفال فيه.
     *
     * @param  \App\Models\KindergartenClass  $kindergartenClass // استخدام Route Model Binding مع اسم البارامتر الصحيح
     */
    public function show(KindergartenClass $kindergartenClass) // استخدام اسم المتغير المطابق للمسار
    {
        // تحميل الأطفال المرتبطين بالفصل مع pagination
        $children = $kindergartenClass->children()
                                      ->with('parents') // تحميل أولياء الأمور لكل طفل
                                      ->latest()
                                      ->paginate(15);

        return view('web.admin.classes.show', compact('kindergartenClass', 'children'));
    }

    /**
     * Show the form for editing the specified kindergarten class.
     * عرض نموذج تعديل فصل موجود.
     *
     * @param  \App\Models\KindergartenClass  $kindergartenClass
     */
    public function edit(KindergartenClass $kindergartenClass)
    {
        return view('web.admin.classes.edit', compact('kindergartenClass'));
    }

    /**
     * Update the specified kindergarten class in storage.
     * تحديث بيانات الفصل.
     *
     * @param  \App\Http\Requests\Admin\UpdateKindergartenClassRequest  $request
     * @param  \App\Models\KindergartenClass  $kindergartenClass
     */
    public function update(UpdateKindergartenClassRequest $request, KindergartenClass $kindergartenClass)
    {
        // الحصول على البيانات المتحقق منها
        $validated = $request->validated();

        // تحديث الفصل
        $kindergartenClass->update($validated);

        // إعادة التوجيه مع رسالة نجاح
        return redirect()->route('admin.classes.index')
                         ->with('success', 'تم تحديث الفصل بنجاح.');
    }

    /**
     * Remove the specified kindergarten class from storage.
     * حذف الفصل (مع التحقق من عدم وجود أطفال مرتبطين به).
     *
     * @param  \App\Models\KindergartenClass  $kindergartenClass
     */
    public function destroy(KindergartenClass $kindergartenClass)
    {
        // التحقق من وجود أطفال مرتبطين بهذا الفصل
        // نستخدم count() بدلاً من exists() للحصول على العدد الفعلي إذا أردنا عرضه في رسالة الخطأ
        $childCount = $kindergartenClass->children()->count();

        if ($childCount > 0) {
            // إذا كان هناك أطفال، لا تسمح بالحذف وأظهر رسالة خطأ
            return redirect()->route('admin.classes.index')
                             ->with('error', "لا يمكن حذف الفصل '{$kindergartenClass->class_name}' لأنه يحتوي على ({$childCount}) طفل/أطفال. يرجى نقل الأطفال إلى فصل آخر أولاً.");
        }

        // إذا لم يكن هناك أطفال، قم بالحذف
        try {
            // تأكد من أن علاقات أخرى (مثل الوجبات، الجداول) معرفة بـ ON DELETE SET NULL أو CASCADE
            $kindergartenClass->delete();
            return redirect()->route('admin.classes.index')
                             ->with('success', 'تم حذف الفصل بنجاح.');
        } catch (\Exception $e) {
            // معالجة أي أخطاء أخرى قد تحدث
             return redirect()->route('admin.classes.index')
                             ->with('error', 'فشل حذف الفصل. قد يكون مرتبطًا بسجلات أخرى.');
        }
    }
}