<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthRecord;
use Illuminate\Http\Request; // <-- إضافة Request
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\HealthRecordResource;
use App\Models\ParentModel; // <-- يمكنك استيراد ParentModel إذا أردت

class HealthRecordController extends Controller
{
    /**
     * Display health records for the parent's children with pagination.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request) // <-- إضافة Request
    {
        $user = Auth::user();
        $parent = $user ? $user->parentProfile : null;

        if (!$parent) {
             return response()->json(['error' => 'Parent profile not found.'], 404);
        }

        // ---=== تعديل هنا لتحديد الجدول ===---
        $childIds = $parent->children()->pluck('children.child_id'); // تحديد children.child_id
        // ---=== نهاية التعديل ===---

        if ($childIds->isEmpty()) {
            // استخدام collection فارغة مع paginate لتجنب الأخطاء والحفاظ على بنية الرد
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, $request->query('per_page', 15), 1,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            );
            return HealthRecordResource::collection($emptyPaginator);
        }

        // --- إضافة Pagination ---
        $perPage = $request->query('per_page', 15); // عدد السجلات لكل صفحة

        $healthRecords = HealthRecord::with(['child', 'enteredByUser']) // تحميل العلاقات
                           ->whereIn('child_id', $childIds->all()) // استخدام all() لتحويل Collection إلى array
                           ->orderBy('record_date', 'desc')
                           ->orderBy('created_at', 'desc') // ترتيب ثانوي حسب وقت الإنشاء
                           ->paginate($perPage);

        return HealthRecordResource::collection($healthRecords);
    }

    // show, store, update, destroy likely not needed for parents via API
    // (Maybe allow parents to add certain records? If so, implement store with validation/auth)
}