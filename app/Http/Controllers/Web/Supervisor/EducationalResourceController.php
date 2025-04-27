<?php

namespace App\Http\Controllers\Web\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\EducationalResource;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
// --- استيراد ملفات الطلبات الصحيحة ---
use App\Http\Requests\Supervisor\StoreEducationalResourceRequest; // <-- تغيير هنا
use App\Http\Requests\Supervisor\UpdateEducationalResourceRequest; // <-- تغيير هنا

class EducationalResourceController extends Controller
{
    // --- لا حاجة لـ canManageResources إذا اعتمدنا على Form Request authorize ---

    /**
     * Display a listing of the educational resources.
     */
    public function index(Request $request)
    {
        // لا يوجد تحقق هنا، نفترض أن المشرف يمكنه عرض القائمة
        $query = EducationalResource::with('addedByAdmin.user');

        $searchTerm = $request->query('search');
        if ($searchTerm) {
             $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }
        $resourceType = $request->query('resource_type');
        if ($resourceType) {
            $query->where('resource_type', $resourceType);
        }
        $subject = $request->query('subject');
        if ($subject) {
             $query->where('subject', 'like', "%{$subject}%");
        }

        $resources = $query->latest('created_at')->paginate(15)->withQueryString();
        $resourceTypes = ['Video' => 'فيديو', 'Article' => 'مقالة', 'Game' => 'لعبة', 'Link' => 'رابط'];

        return view('web.supervisor.resources.index', compact(
            'resources', 'resourceTypes', 'searchTerm', 'resourceType', 'subject'
        ));
    }

    /**
     * Show the form for creating a new educational resource.
     */
    public function create()
    {
        // --- استخدام authorizeResource المدمج (أو authorize يدويًا) ---
        // هذا يفترض أنك قمت بتسجيل Policy (الطريقة المفضلة)
        // $this->authorize('create', EducationalResource::class);

        // أو التحقق المباشر من الدور (إذا لم تستخدم Policy)
        if (!(Auth::check() && Auth::user()->role === 'Supervisor')) {
             abort(403);
        }

        $resourceTypes = ['Video' => 'فيديو', 'Article' => 'مقالة', 'Game' => 'لعبة', 'Link' => 'رابط'];
        return view('web.supervisor.resources.create', compact('resourceTypes'));
    }

    /**
     * Store a newly created educational resource in storage.
     */
    // *** استخدام Form Request الصحيح للمشرف ***
    public function store(StoreEducationalResourceRequest $request)
    {
        // التحقق من الصلاحية تم في Form Request authorize()

        $validated = $request->validated();
        // نفس منطق البحث عن admin profile
        $adminProfile = Auth::user()->adminProfile ?? Admin::first();
        if (!$adminProfile) {
             return back()->with('error', 'Cannot determine resource author profile.')->withInput();
        }

        EducationalResource::create(array_merge($validated, [
             'added_by_id' => $adminProfile->admin_id,
        ]));

        return redirect()->route('supervisor.resources.index')
                         ->with('success', 'تم إضافة المصدر التعليمي بنجاح.');
    }

    /**
     * Display the specified resource.
     */
    public function show(EducationalResource $educationalResource)
    {
        // التحقق من صلاحية العرض (عادةً مسموح)
        // $this->authorize('view', $educationalResource); // إذا استخدمت Policy

        $educationalResource->load('addedByAdmin.user');
        return view('web.supervisor.resources.show', ['resource' => $educationalResource]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EducationalResource $educationalResource)
    {
        // التحقق من صلاحية التعديل (من خلال authorizeResource أو يدويًا)
        // $this->authorize('update', $educationalResource); // إذا استخدمت Policy

        // أو التحقق المباشر
        if (!(Auth::check() && Auth::user()->role === 'Supervisor')) {
            abort(403);
        }
        // يمكنك إضافة تحقق إضافي من الملكية هنا إذا أردت

        $resourceTypes = ['Video' => 'فيديو', 'Article' => 'مقالة', 'Game' => 'لعبة', 'Link' => 'رابط'];
        return view('web.supervisor.resources.edit', ['resource' => $educationalResource, 'resourceTypes' => $resourceTypes]);
    }

    /**
     * Update the specified resource in storage.
     */
    // *** استخدام Form Request الصحيح للمشرف ***
    public function update(UpdateEducationalResourceRequest $request, EducationalResource $educationalResource)
    {
        // التحقق من الصلاحية تم في Form Request authorize()

        $validated = $request->validated();
        $educationalResource->update($validated);

        return redirect()->route('supervisor.resources.index')
                         ->with('success', 'تم تحديث المصدر التعليمي بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EducationalResource $educationalResource)
    {
        // التحقق من صلاحية الحذف
        // $this->authorize('delete', $educationalResource); // إذا استخدمت Policy

        // أو التحقق المباشر
         if (!(Auth::check() && Auth::user()->role === 'Supervisor')) {
             abort(403);
         }
         // يمكنك إضافة تحقق إضافي من الملكية هنا إذا أردت

        try {
            $educationalResource->delete();
            return redirect()->route('supervisor.resources.index')
                             ->with('success', 'تم حذف المصدر التعليمي بنجاح.');
        } catch (\Exception $e) {
             return redirect()->route('supervisor.resources.index')
                             ->with('error', 'فشل حذف المصدر التعليمي.');
        }
    }
}