<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EducationalResource;
use Illuminate\Http\Request;
use App\Http\Resources\EducationalResource as EducationalResourceResource; // Alias if needed

class EducationalResourceController extends Controller
{
    /**
     * Display a listing of educational resources with pagination.
     * Optionally filterable by subject or age range in the future.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        // --- تنفيذ TODO: Pagination ---
        // عدد العناصر لكل صفحة (يمكن جعله قابلاً للتكوين أو أخذه من الطلب)
        $perPage = $request->query('per_page', 15); // الافتراضي 15 عنصرًا لكل صفحة

        // Basic listing, could add filtering by age/subject later
        $resources = EducationalResource::with('addedByAdmin') // تحميل علاقة المدير لتحسين الأداء
                     ->latest('added_at') // الترتيب حسب الأحدث
                     ->paginate($perPage); // استخدام paginate بدلاً من get

        // --- ملاحظة حول الفلترة المستقبلية (اختياري) ---
        /*
        // مثال للفلترة حسب الموضوع (subject) إذا تم إرساله كـ query parameter
        if ($request->has('subject')) {
            $resources = EducationalResource::with('addedByAdmin')
                         ->where('subject', 'like', '%' . $request->query('subject') . '%') // بحث بسيط
                         ->latest('added_at')
                         ->paginate($perPage);
        }

        // مثال للفلترة حسب العمر (إذا تم إرسال age كـ query parameter)
        if ($request->has('age') && is_numeric($request->query('age'))) {
            $age = $request->query('age');
            $resources = EducationalResource::with('addedByAdmin')
                         ->where(function($query) use ($age) {
                             $query->where('target_age_min', '<=', $age)
                                   ->where('target_age_max', '>=', $age);
                         })
                         ->orWhere(function($query){ // Include resources without specific age range
                             $query->whereNull('target_age_min')->whereNull('target_age_max');
                         })
                         ->latest('added_at')
                         ->paginate($perPage);
        }
        */
        // -----------------------------------------------------

        // عند استخدام paginate مع Resource Collection، يقوم Laravel تلقائيًا بتضمين بيانات الـ pagination
        return EducationalResourceResource::collection($resources);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EducationalResource  $educationalResource
     * @return \App\Http\Resources\EducationalResource
     */
    public function show(EducationalResource $educationalResource)
    {
         // تحميل العلاقة قبل إرسالها إلى الريسورس
         $educationalResource->load('addedByAdmin');
         return new EducationalResourceResource($educationalResource);
    }

     // store, update, destroy not needed for parents
}