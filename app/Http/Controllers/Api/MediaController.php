<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request; // <-- إضافة استيراد Request
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\MediaResource;

class MediaController extends Controller
{
    /**
     * Display media relevant to the parent with pagination.
     * (Linked to their children OR their children's classes OR associated events OR general)
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

        // الحصول على معرفات أطفال ولي الأمر ومعرفات فصولهم
        $childIds = $parent->children()->pluck('child_id')->toArray();
        $childClassIds = $parent->children()->pluck('class_id')->unique()->filter()->toArray();

        // --- (اختياري ولكن مقترح) الحصول على معرفات الفعاليات التي سجل فيها أطفال ولي الأمر ---
        $registeredEventIds = \App\Models\EventRegistration::whereIn('child_id', $childIds)
                                ->pluck('event_id')
                                ->unique()
                                ->toArray();
        // ------------------------------------------------------------------------------

        // --- تنفيذ TODO: Add pagination ---
        $perPage = $request->query('per_page', 12); // عدد عناصر الوسائط لكل صفحة، افتراضي 12

        // بناء الاستعلام المعقد
        $query = Media::with(['uploader', 'associatedChild', 'associatedEvent', 'associatedClass'])
            ->where(function ($query) use ($childIds, $childClassIds, $registeredEventIds) {
                // 1. الوسائط المرتبطة مباشرة بأطفال ولي الأمر
                $query->whereIn('associated_child_id', $childIds)
                // 2. أو الوسائط المرتبطة بفصول أطفال ولي الأمر
                      ->orWhereIn('associated_class_id', $childClassIds)
                // 3. أو الوسائط المرتبطة بفعاليات سجل فيها أطفال ولي الأمر (إذا كانت القائمة غير فارغة)
                      ->when(!empty($registeredEventIds), function ($q) use ($registeredEventIds) {
                          return $q->orWhereIn('associated_event_id', $registeredEventIds);
                      })
                // 4. أو الوسائط العامة (غير مرتبطة بأي طفل أو فصل أو فعالية)
                      ->orWhere(function($q) {
                          $q->whereNull('associated_child_id')
                            ->whereNull('associated_class_id')
                            ->whereNull('associated_event_id');
                          });
            })
            ->latest('upload_date'); // الترتيب حسب الأحدث

        // تطبيق الـ pagination
        $media = $query->paginate($perPage);

        // إرجاع النتائج باستخدام الريسورس
        return MediaResource::collection($media);
    }

    /**
     * Display specific media item (if parent has access).
     *
     * @param  \App\Models\Media  $medium
     * @return \App\Http\Resources\MediaResource|\Illuminate\Http\JsonResponse
     */
    public function show(Media $medium)
    {
        $user = Auth::user();
        $parent = $user ? $user->parentProfile : null;

        // إذا لم يكن المستخدم ولي أمر، لا يمكنه رؤية أي وسائط خاصة
        if (!$parent) {
             return response()->json(['error' => 'Unauthorized'], 403);
        }

        // الحصول على معرفات أطفال ولي الأمر ومعرفات فصولهم ومعرفات فعالياتهم المسجلين بها
        $childIds = $parent->children()->pluck('child_id')->toArray();
        $childClassIds = $parent->children()->pluck('class_id')->unique()->filter()->toArray();
        $registeredEventIds = \App\Models\EventRegistration::whereIn('child_id', $childIds)
                                ->pluck('event_id')
                                ->unique()
                                ->toArray();

        // التحقق من الصلاحية: هل الوسائط عامة أو مرتبطة بولي الأمر بطريقة ما؟
        $isGeneral = is_null($medium->associated_child_id) && is_null($medium->associated_class_id) && is_null($medium->associated_event_id);
        $isRelatedToChild = in_array($medium->associated_child_id, $childIds);
        $isRelatedToClass = in_array($medium->associated_class_id, $childClassIds);
        $isRelatedToEvent = in_array($medium->associated_event_id, $registeredEventIds);

        // السماح بالوصول إذا كانت عامة أو مرتبطة بولي الأمر بأي شكل
        if (!($isGeneral || $isRelatedToChild || $isRelatedToClass || $isRelatedToEvent)) {
             return response()->json(['error' => 'Unauthorized'], 403);
        }

        // تحميل العلاقات قبل إرسالها للريسورس
        $medium->load(['uploader', 'associatedChild', 'associatedEvent', 'associatedClass']);
        return new MediaResource($medium);
    }

     // store, update, destroy not needed for parents via API
}