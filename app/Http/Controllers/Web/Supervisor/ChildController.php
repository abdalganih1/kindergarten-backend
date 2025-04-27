<?php

namespace App\Http\Controllers\Web\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\KindergartenClass; // للفلترة
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChildController extends Controller
{
     /**
     * Get the classes supervised by the current user.
     * (Helper function - adapt based on your actual implementation)
     * @return \Illuminate\Support\Collection
     */
    private function getSupervisorClassIds()
    {
        $user = Auth::user();
        // TODO: Implement supervisor class scoping
        // Example: Assume supervisor can see children from all classes for now
         return KindergartenClass::pluck('class_id');
        // return $user->supervisorClasses()->pluck('class_id'); // Or use actual logic
    }

    /**
     * Display a listing of the children in supervised classes.
     * عرض قائمة بأطفال الفصول التي يشرف عليها المستخدم.
     */
    public function index(Request $request)
    {
        $supervisorClassIds = $this->getSupervisorClassIds();

        // إذا لم يكن المشرف مسؤولاً عن أي فصول
        if ($supervisorClassIds->isEmpty()) {
             return view('web.supervisor.children.index', [
                'children' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'supervisedClasses' => collect(),
                'selectedClassId' => null,
                'searchTerm' => null,
                'noClassesAssigned' => true
            ]);
        }

        $query = Child::with(['kindergartenClass', 'parents'])
                       ->whereIn('class_id', $supervisorClassIds); // *** فلترة حسب فصول المشرف ***

        // --- الفلترة والبحث ---
        // 1. البحث بالاسم
        $searchTerm = $request->query('search');
        if ($searchTerm) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('last_name', 'like', "%{$searchTerm}%");
            });
        }

        // 2. الفلترة حسب الفصل (من ضمن فصول المشرف)
        $selectedClassId = $request->query('class_id');
        if ($selectedClassId && $supervisorClassIds->contains($selectedClassId)) {
            $query->where('class_id', $selectedClassId);
        } elseif ($selectedClassId) {
             $query->whereRaw('1 = 0'); // لا تعرض شيئًا إذا حاول اختيار فصل غير مسموح به
        }

        // --- الترتيب والـ Pagination ---
        $children = $query->orderBy('first_name')->orderBy('last_name') // الترتيب حسب الاسم
                          ->paginate(15)
                          ->withQueryString();

        // --- بيانات إضافية للـ View (فصول المشرف فقط) ---
        $supervisedClasses = KindergartenClass::whereIn('class_id', $supervisorClassIds)
                                         ->orderBy('class_name')
                                         ->pluck('class_name', 'class_id');

        return view('web.supervisor.children.index', compact(
            'children',
            'supervisedClasses',
            'searchTerm',
            'selectedClassId'
        ));
    }


    /**
     * Display the specified child (if in a supervised class).
     * عرض تفاصيل طفل معين إذا كان ينتمي لأحد فصول المشرف.
     *
     * @param  \App\Models\Child  $child // استخدام Route Model Binding
     */
    public function show(Child $child)
    {
        $supervisorClassIds = $this->getSupervisorClassIds();

        // *** التحقق من الصلاحية: هل الطفل في فصل يشرف عليه المستخدم؟ ***
        if (!$supervisorClassIds->contains($child->class_id)) {
             abort(403, 'Unauthorized access to this child\'s profile.');
        }

        // تحميل العلاقات المطلوبة للعرض التفصيلي (مشابهة للمدير)
         $child->load([
             'kindergartenClass',
             'parents.user',
             'healthRecords.enteredByUser',
             'eventRegistrations.event',
             'attendances.recordedByUser',
             'observations.parentSubmitter'
            ]);

        return view('web.supervisor.children.show', compact('child'));
    }

    // الدوال create, store, edit, update, destroy غير مطلوبة للمشرف هنا
}