<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\KindergartenClass;
use App\Models\ParentModel; // Use correct name
use Illuminate\Http\Request; // <-- إضافة Request
use App\Http\Requests\Admin\StoreChildRequest;
use App\Http\Requests\Admin\UpdateChildRequest;
use Illuminate\Support\Facades\Storage;

class ChildController extends Controller
{
    /**
     * Display a listing of the children with pagination, search, and filtering.
     */
    public function index(Request $request) // <-- إضافة Request
    {
        // --- تنفيذ TODO: Pagination, Search, Filtering ---
        $query = Child::with(['kindergartenClass', 'parents']) // تحميل العلاقات الأساسية
                       ->select('children.*'); // تحديد الجدول لتجنب تضارب الأعمدة عند الربط

        // 1. البحث عن اسم الطفل (الأول أو الأخير)
        $searchTerm = $request->query('search');
        if ($searchTerm) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('children.first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('children.last_name', 'like', "%{$searchTerm}%");
            });
        }

        // 2. الفلترة حسب الفصل الدراسي
        $selectedClassId = $request->query('class_id');
        if ($selectedClassId) {
            $query->where('children.class_id', $selectedClassId);
        }

        // 3. الفلترة حسب ولي الأمر (يتطلب ربط مع جدول الآباء)
        $selectedParentId = $request->query('parent_id');
        if ($selectedParentId) {
            // استخدام whereHas للفلترة بناءً على علاقة parents
            $query->whereHas('parents', function ($q) use ($selectedParentId) {
                $q->where('parents.parent_id', $selectedParentId);
            });
        }

        // --- الترتيب والـ Pagination ---
        $children = $query->latest('children.created_at') // الترتيب حسب تاريخ الإنشاء الأحدث
                           ->paginate(15) // عرض 15 طفلًا في الصفحة
                           ->withQueryString(); // للحفاظ على query parameters عند التنقل

        // --- بيانات إضافية للـ View (قوائم الفلترة) ---
        $classes = KindergartenClass::orderBy('class_name')->pluck('class_name', 'class_id');
        $parents = ParentModel::orderBy('full_name')->pluck('full_name', 'parent_id');


        // إرسال البيانات إلى الواجهة
        return view('web.admin.children.index', compact(
            'children',
            'classes',
            'parents',
            'searchTerm',
            'selectedClassId',
            'selectedParentId'
        )); // تأكد من إنشاء هذا الـ view
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $classes = KindergartenClass::orderBy('class_name')->pluck('class_name', 'class_id');
        $parents = ParentModel::orderBy('full_name')->pluck('full_name', 'parent_id'); // For assigning parents
        return view('web.admin.children.create', compact('classes', 'parents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChildRequest $request)
    {
        $validated = $request->validated();
        $photoPath = null;

        // Handle file upload
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $photoPath = $request->file('photo')->store('children_photos', 'public');
            $validated['photo_url'] = $photoPath; // Store the path
        }

        $child = Child::create($validated);

        // Attach parents if provided
        if (!empty($validated['parent_ids'])) {
            $child->parents()->sync($validated['parent_ids']);
        }

        return redirect()->route('admin.children.index')->with('success', 'تم إنشاء ملف الطفل بنجاح.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Child $child)
    {
         // تحميل العلاقات المطلوبة للعرض التفصيلي
         $child->load([
             'kindergartenClass',
             'parents.user', // تحميل المستخدم المرتبط بولي الأمر
             'healthRecords.enteredByUser', // تحميل المستخدم الذي أدخل السجل الصحي
             'eventRegistrations.event', // تحميل الفعالية المرتبطة بالتسجيل
             'attendances.recordedByUser', // تحميل المستخدم الذي سجل الحضور
             'observations.parentSubmitter' // تحميل ولي الأمر الذي قدم الملاحظة
            ]);
         return view('web.admin.children.show', compact('child'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Child $child)
    {
        $classes = KindergartenClass::orderBy('class_name')->pluck('class_name', 'class_id');
        $parents = ParentModel::orderBy('full_name')->pluck('full_name', 'parent_id');
        $childParentIds = $child->parents()->pluck('parents.parent_id')->toArray(); // Get current parent IDs
        return view('web.admin.children.edit', compact('child', 'classes', 'parents', 'childParentIds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChildRequest $request, Child $child)
    {
        $validated = $request->validated();
        //$photoPath = $child->photo_url; // This line is not needed

        // Handle file upload
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
             // Delete old photo if it exists
            if ($child->photo_url && Storage::disk('public')->exists($child->photo_url)) {
                Storage::disk('public')->delete($child->photo_url);
            }
            $photoPath = $request->file('photo')->store('children_photos', 'public');
            $validated['photo_url'] = $photoPath;
        } elseif ($request->boolean('remove_photo')) { // Checkbox to remove photo
             if ($child->photo_url && Storage::disk('public')->exists($child->photo_url)) {
                Storage::disk('public')->delete($child->photo_url);
            }
            $validated['photo_url'] = null;
        }
        // If no new photo and remove_photo is not checked, photo_url is not included in $validated, so it remains unchanged.

        $child->update($validated);

         // Sync parents
        $child->parents()->sync($validated['parent_ids'] ?? []); // Use empty array if none selected


        return redirect()->route('admin.children.index')->with('success', 'تم تحديث ملف الطفل بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Child $child)
    {
         // Delete photo if it exists
        if ($child->photo_url && Storage::disk('public')->exists($child->photo_url)) {
            Storage::disk('public')->delete($child->photo_url);
        }

        // Detach parents relationship before deleting the child to avoid potential FK issues
        // if cascade on delete is not set or reliable. However, syncing with empty array
        // before delete might be safer in some DB configurations.
        // $child->parents()->sync([]); // Optional: detach explicitly

        try {
            $child->delete(); // Related records like attendance, health records should cascade based on FK definition
            return redirect()->route('admin.children.index')->with('success', 'تم حذف ملف الطفل بنجاح.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle potential foreign key constraint errors if cascade fails
            return back()->with('error', 'لا يمكن حذف الطفل لوجود سجلات مرتبطة به (مثل التسجيل في فعاليات). يرجى حذف السجلات المرتبطة أولاً.');
        } catch (\Exception $e) {
             return back()->with('error', 'حدث خطأ أثناء محاولة حذف الطفل.');
        }
    }
}