<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\EducationalResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // لجلب هوية المدير
use Illuminate\Validation\Rule; // لاستخدام قواعد التحقق المتقدمة
use App\Http\Requests\Admin\StoreEducationalResourceRequest; // يجب إنشاؤه
use App\Http\Requests\Admin\UpdateEducationalResourceRequest; // يجب إنشاؤه

class EducationalResourceController extends Controller
{
    /**
     * Display a listing of the educational resources.
     * عرض قائمة بالمصادر التعليمية مع الفلترة والبحث.
     */
    public function index(Request $request)
    {
        $query = EducationalResource::with('addedByAdmin.user'); // تحميل علاقة المدير والمستخدم المرتبط به

        // --- الفلترة والبحث ---
        // 1. البحث عن عنوان أو وصف
        $searchTerm = $request->query('search');
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // 2. الفلترة حسب نوع المصدر
        $resourceType = $request->query('resource_type');
        if ($resourceType) {
            $query->where('resource_type', $resourceType);
        }

        // 3. الفلترة حسب الموضوع
        $subject = $request->query('subject');
        if ($subject) {
            $query->where('subject', 'like', "%{$subject}%");
        }

        // --- الترتيب والـ Pagination ---
        $resources = $query->latest('created_at') // الترتيب حسب تاريخ الإضافة الأحدث
                           ->paginate(15)
                           ->withQueryString();

        // قائمة بأنواع المصادر للفلترة
        $resourceTypes = ['Video' => 'فيديو', 'Article' => 'مقالة', 'Game' => 'لعبة', 'Link' => 'رابط'];

        // إرسال البيانات للواجهة
        return view('web.admin.resources.index', compact(
            'resources',
            'resourceTypes',
            'searchTerm',
            'resourceType',
            'subject'
        ));
    }

    /**
     * Show the form for creating a new educational resource.
     * عرض نموذج إضافة مصدر تعليمي جديد.
     */
    public function create()
    {
        // قائمة بأنواع المصادر للنموذج
        $resourceTypes = ['Video' => 'فيديو', 'Article' => 'مقالة', 'Game' => 'لعبة', 'Link' => 'رابط'];
        return view('web.admin.resources.create', compact('resourceTypes'));
    }

    /**
     * Store a newly created educational resource in storage.
     * تخزين المصدر التعليمي الجديد.
     *
     * @param  \App\Http\Requests\Admin\StoreEducationalResourceRequest  $request
     */
    public function store(StoreEducationalResourceRequest $request)
    {
        $validated = $request->validated();
        $admin = Auth::user()->adminProfile;

        if (!$admin) {
             return back()->with('error', 'Admin profile not found.')->withInput();
        }

        EducationalResource::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'resource_type' => $validated['resource_type'],
            'url_or_path' => $validated['url_or_path'],
            'target_age_min' => $validated['target_age_min'] ?? null,
            'target_age_max' => $validated['target_age_max'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'added_by_id' => $admin->admin_id,
             // added_at يتم تعيينه تلقائيًا بواسطة timestamps()
        ]);

        return redirect()->route('admin.resources.index')
                         ->with('success', 'تم إضافة المصدر التعليمي بنجاح.');
    }

    /**
     * Display the specified educational resource. (Optional)
     * عرض تفاصيل مصدر تعليمي محدد (يمكن الاكتفاء بالعرض في القائمة).
     *
     * @param  \App\Models\EducationalResource  $resource // استخدام Route Model Binding
     */
    public function show(EducationalResource $educationalResource) // <-- تأكد أن الاسم هنا educationalResource
    {
        $educationalResource->load('addedByAdmin.user');
        // استخدام $educationalResource بدلًا من $resource في compact
        return view('web.admin.resources.show', compact('educationalResource'));
    }

    /**
     * Show the form for editing the specified educational resource.
     * عرض نموذج تعديل مصدر تعليمي موجود.
     *
     * @param  \App\Models\EducationalResource  $educationalResource
     */
    public function edit(EducationalResource $educationalResource)
    {
         // قائمة بأنواع المصادر للنموذج
        $resourceTypes = ['Video' => 'فيديو', 'Article' => 'مقالة', 'Game' => 'لعبة', 'Link' => 'رابط'];
        return view('web.admin.resources.edit', compact('educationalResource', 'resourceTypes'));
    }

    /**
     * Update the specified educational resource in storage.
     * تحديث بيانات المصدر التعليمي.
     *
     * @param  \App\Http\Requests\Admin\UpdateEducationalResourceRequest  $request
     * @param  \App\Models\EducationalResource  $educationalResource
     */
    public function update(UpdateEducationalResourceRequest $request, EducationalResource $educationalResource)
    {
        $validated = $request->validated();

        // تحديث المصدر
        $educationalResource->update([
             'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'resource_type' => $validated['resource_type'],
            'url_or_path' => $validated['url_or_path'],
            'target_age_min' => $validated['target_age_min'] ?? null,
            'target_age_max' => $validated['target_age_max'] ?? null,
            'subject' => $validated['subject'] ?? null,
            // لا نحدث added_by_id عادةً
        ]);

         return redirect()->route('admin.resources.index')
                         ->with('success', 'تم تحديث المصدر التعليمي بنجاح.');
    }

    /**
     * Remove the specified educational resource from storage.
     * حذف المصدر التعليمي.
     *
     * @param  \App\Models\EducationalResource  $educationalResource
     */
    public function destroy(EducationalResource $educationalResource)
    {
        try {
            // قد تحتاج للتحقق من عدم وجود ارتباطات أخرى بالمصدر قبل الحذف إذا لزم الأمر
            $educationalResource->delete();
            return redirect()->route('admin.resources.index')
                             ->with('success', 'تم حذف المصدر التعليمي بنجاح.');
        } catch (\Exception $e) {
             return redirect()->route('admin.resources.index')
                             ->with('error', 'فشل حذف المصدر التعليمي. يرجى المحاولة مرة أخرى.');
        }
    }
}