<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use App\Models\Child; // للفلترة
use App\Models\ParentModel; // للفلترة
use Illuminate\Http\Request;

class ObservationController extends Controller
{
    /**
     * Display a listing of the observations from parents.
     * عرض قائمة بملاحظات أولياء الأمور مع الفلترة.
     */
    public function index(Request $request)
    {
        $query = Observation::with(['parentSubmitter.user', 'child']); // تحميل العلاقات اللازمة

        // --- الفلترة ---
        // 1. الفلترة حسب ولي الأمر
        $parentId = $request->query('parent_id');
        if ($parentId) {
            $query->where('parent_id', $parentId);
        }

        // 2. الفلترة حسب الطفل
        $childId = $request->query('child_id');
        if ($childId) {
            $query->where('child_id', $childId);
        }

         // 3. البحث في نص الملاحظة
        $searchTerm = $request->query('search');
        if ($searchTerm) {
             $query->where('observation_text', 'like', "%{$searchTerm}%");
        }

        // --- الترتيب والـ Pagination ---
        $observations = $query->latest('submitted_at') // الأحدث أولاً
                              ->paginate(15)
                              ->withQueryString();

        // --- بيانات إضافية للـ View (للفلاتر) ---
        $parents = ParentModel::orderBy('full_name')->pluck('full_name', 'parent_id');
        $children = Child::orderBy('first_name')->get(['child_id', 'first_name', 'last_name'])->mapWithKeys(function ($child) {
             return [$child->child_id => $child->full_name]; // إنشاء مصفوفة ID => Full Name
        });

        // إرسال البيانات للواجهة
        return view('web.admin.observations.index', compact(
            'observations',
            'parents',
            'children',
            'parentId',
            'childId',
            'searchTerm'
        ));
    }

    /**
     * Display the specified observation.
     * عرض تفاصيل ملاحظة محددة.
     *
     * @param  \App\Models\Observation  $observation // استخدام Route Model Binding
     */
    public function show(Observation $observation)
    {
        $observation->load(['parentSubmitter.user', 'child.kindergartenClass']); // تحميل العلاقات
        return view('web.admin.observations.show', compact('observation'));
    }

    /**
     * Remove the specified observation from storage.
     * حذف الملاحظة.
     *
     * @param  \App\Models\Observation  $observation
     */
    public function destroy(Observation $observation)
    {
        try {
            $observation->delete();
            return redirect()->route('admin.observations.index')
                             ->with('success', 'تم حذف الملاحظة بنجاح.');
        } catch (\Exception $e) {
             return redirect()->route('admin.observations.index')
                             ->with('error', 'فشل حذف الملاحظة. يرجى المحاولة مرة أخرى.');
        }
    }

    // الدوال create, store, edit, update غير مطلوبة للمدير هنا
}