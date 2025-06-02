<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\EducationalResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // لجلب هوية المستخدم
use Illuminate\Validation\Rule;
use App\Http\Requests\Admin\StoreEducationalResourceRequest;
use App\Http\Requests\Admin\UpdateEducationalResourceRequest;

class EducationalResourceController extends Controller
{
    /**
     * Display a listing of the educational resources.
     */
    public function index(Request $request)
    {
        // ---=== تعديل هنا: استخدام addedByUser ===---
        $query = EducationalResource::with('addedByUser'); // تحميل علاقة المستخدم الذي أضاف المصدر
        // ---=== نهاية التعديل ===---

        // ... (بقية كود الفلترة) ...
        $searchTerm = $request->query('search');
        if ($searchTerm) { /* ... */ }
        $resourceType = $request->query('resource_type');
        if ($resourceType) { /* ... */ }
        $subject = $request->query('subject');
        if ($subject) { /* ... */ }

        $resources = $query->latest('created_at') // الترتيب حسب created_at (الذي يمثل added_at)
                           ->paginate(15)
                           ->withQueryString();

        $resourceTypes = ['Video' => 'فيديو', 'Article' => 'مقالة', 'Game' => 'لعبة', 'Link' => 'رابط'];

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
     */
    public function create()
    {
        $resourceTypes = ['Video' => 'فيديو', 'Article' => 'مقالة', 'Game' => 'لعبة', 'Link' => 'رابط'];
        return view('web.admin.resources.create', compact('resourceTypes'));
    }

    /**
     * Store a newly created educational resource in storage.
     */
    public function store(StoreEducationalResourceRequest $request)
    {
        $validated = $request->validated();
        // $admin = Auth::user()->adminProfile; // لم نعد نستخدم adminProfile
        $adderId = Auth::id(); // نستخدم user_id للمضيف مباشرة

        // if (!$admin) { // لم يعد هذا الشرط ضروريًا بهذه الطريقة
        //     return back()->with('error', 'Admin profile not found.')->withInput();
        // }

        EducationalResource::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'resource_type' => $validated['resource_type'],
            'url_or_path' => $validated['url_or_path'],
            'target_age_min' => $validated['target_age_min'] ?? null,
            'target_age_max' => $validated['target_age_max'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'added_by_id' => $adderId, // <-- تم التعديل هنا
            // created_at هو added_at
        ]);

        return redirect()->route('admin.resources.index')
                         ->with('success', 'تم إضافة المصدر التعليمي بنجاح.');
    }

    /**
     * Display the specified educational resource.
     */
    public function show(EducationalResource $educationalResource)
    {
        // ---=== تعديل هنا: استخدام addedByUser ===---
        $educationalResource->load('addedByUser');
        // ---=== نهاية التعديل ===---
        return view('web.admin.resources.show', compact('educationalResource'));
    }

    /**
     * Show the form for editing the specified educational resource.
     */
    public function edit(EducationalResource $educationalResource)
    {
        $resourceTypes = ['Video' => 'فيديو', 'Article' => 'مقالة', 'Game' => 'لعبة', 'Link' => 'رابط'];
        return view('web.admin.resources.edit', compact('educationalResource', 'resourceTypes'));
    }

    /**
     * Update the specified educational resource in storage.
     */
    public function update(UpdateEducationalResourceRequest $request, EducationalResource $educationalResource)
    {
        $validated = $request->validated();
        $educationalResource->update($validated); // created_by_id لا يتم تحديثه عادةً

         return redirect()->route('admin.resources.index')
                         ->with('success', 'تم تحديث المصدر التعليمي بنجاح.');
    }

    /**
     * Remove the specified educational resource from storage.
     */
    public function destroy(EducationalResource $educationalResource)
    {
        try {
            $educationalResource->delete();
            return redirect()->route('admin.resources.index')
                             ->with('success', 'تم حذف المصدر التعليمي بنجاح.');
        } catch (\Exception $e) {
             return redirect()->route('admin.resources.index')
                             ->with('error', 'فشل حذف المصدر التعليمي. يرجى المحاولة مرة أخرى.');
        }
    }
}