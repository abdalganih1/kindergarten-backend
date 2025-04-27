<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Http\Resources\ObservationResource;

class ObservationController extends Controller
{
    /**
     * Display observations submitted by the authenticated parent with pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request) // <-- إضافة Request $request
    {
        $user = Auth::user();
        $parent = $user ? $user->parentProfile : null;

        if (!$parent) {
             return response()->json(['error' => 'Parent profile not found.'], 404);
        }

        // --- تنفيذ TODO: Pagination ---
        $perPage = $request->query('per_page', 15); // عدد الملاحظات لكل صفحة

        // جلب الملاحظات مع تحميل علاقة الطفل (الملاحظات تخص ولي الأمر الحالي فقط)
        $observations = Observation::with(['child']) // معلومات ولي الأمر معروفة بالفعل
                       ->where('parent_id', $parent->parent_id) // فقط ملاحظات ولي الأمر الحالي
                       ->latest('submitted_at') // الترتيب حسب الأحدث
                       ->paginate($perPage); // تطبيق الـ pagination

        // إرجاع النتائج باستخدام الريسورس
        return ObservationResource::collection($observations);
    }

    /**
     * Store a new observation/feedback from the parent.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\ObservationResource|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $parent = $user ? $user->parentProfile : null;

        if (!$parent) {
             return response()->json(['error' => 'Parent profile not found.'], 404);
        }

        // الحصول على معرفات أطفال ولي الأمر للتحقق
        $parentChildIds = $parent->children()->pluck('child_id');

        // التحقق من صحة الطلب
        $validated = $request->validate([
            'observation_text' => ['required', 'string', 'max:2000'],
            'child_id' => [
                'nullable', // قد تكون الملاحظة عامة غير مرتبطة بطفل معين
                'integer',
                // التأكد من أن الطفل (إذا تم تحديده) ينتمي لولي الأمر
                Rule::exists('children', 'child_id')->where(function ($query) use ($parentChildIds) {
                    $query->whereIn('child_id', $parentChildIds);
                }),
             ],
        ]);

        // إنشاء الملاحظة
        $observation = Observation::create([
            'parent_id' => $parent->parent_id,
            'child_id' => $validated['child_id'] ?? null, // استخدام null إذا لم يتم توفير child_id
            'observation_text' => $validated['observation_text'],
             // submitted_at يأخذ القيمة الافتراضية CURRENT_TIMESTAMP
        ]);

        // إرجاع بيانات الملاحظة الجديدة مع تحميل العلاقات المطلوبة للريسورس
        return new ObservationResource($observation->load(['child', 'parentSubmitter']));
    }

    // --- تنفيذ TODO (اختياري): السماح لولي الأمر بحذف ملاحظاته ---
    /**
     * Remove the specified observation if submitted by the authenticated parent.
     *
     * @param  \App\Models\Observation  $observation
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Observation $observation)
    {
        $user = Auth::user();
        $parent = $user ? $user->parentProfile : null;

        // التحقق من أن المستخدم هو ولي الأمر الذي قدم الملاحظة
        if (!$parent || $observation->parent_id !== $parent->parent_id) {
            return response()->json(['error' => 'Unauthorized to delete this observation.'], 403);
        }

        // حذف الملاحظة
        $observation->delete();

        // إرجاع رسالة نجاح
        return response()->json(['message' => 'Observation deleted successfully.'], 200);
    }

    // show, update not typically needed for observations from the parent's perspective via API
    // (Admin/Supervisor might have a 'show' view in their web interface)
}