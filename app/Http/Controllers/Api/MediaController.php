<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\MediaResource;
use App\Models\EventRegistration; // <-- استيراد EventRegistration

class MediaController extends Controller
{
    /**
     * Display media relevant to the parent with pagination.
     * (Linked to their children OR their children's classes OR associated events OR general)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $parent = $user ? $user->parentProfile : null;

        if (!$parent) {
            return response()->json(['error' => 'Parent profile not found.'], 404);
        }

        // الحصول على معرفات أطفال ولي الأمر ومعرفات فصولهم
        // ---=== تعديل هنا لتحديد الجدول ===---
        $childIds = $parent->children()->pluck('children.child_id')->toArray();
        $childClassIds = $parent->children()->pluck('children.class_id')->unique()->filter()->toArray();
        // ---=== نهاية التعديل ===---


        // --- (اختياري ولكن مقترح) الحصول على معرفات الفعاليات التي سجل فيها أطفال ولي الأمر ---
        // التأكد من أن $childIds ليست فارغة قبل استخدامها في whereIn
        $registeredEventIds = [];
        if (!empty($childIds)) {
            $registeredEventIds = EventRegistration::whereIn('child_id', $childIds)
                                    ->pluck('event_id')
                                    ->unique()
                                    ->toArray();
        }
        // ------------------------------------------------------------------------------

        $perPage = $request->query('per_page', 12);

        $query = Media::with(['uploader', 'associatedChild', 'associatedEvent', 'associatedClass'])
            ->where(function ($query) use ($childIds, $childClassIds, $registeredEventIds) {
                // 1. الوسائط المرتبطة مباشرة بأطفال ولي الأمر (تأكد أن childIds ليست فارغة)
                if (!empty($childIds)) {
                    $query->whereIn('associated_child_id', $childIds);
                }

                // 2. أو الوسائط المرتبطة بفصول أطفال ولي الأمر (تأكد أن childClassIds ليست فارغة)
                if (!empty($childClassIds)) {
                    $query->orWhereIn('associated_class_id', $childClassIds);
                }

                // 3. أو الوسائط المرتبطة بفعاليات سجل فيها أطفال ولي الأمر
                if (!empty($registeredEventIds)) {
                    $query->orWhereIn('associated_event_id', $registeredEventIds);
                }

                // 4. أو الوسائط العامة
                $query->orWhere(function($q) {
                    $q->whereNull('associated_child_id')
                      ->whereNull('associated_class_id')
                      ->whereNull('associated_event_id');
                });
            })
            ->latest('upload_date');

        $media = $query->paginate($perPage);

        return MediaResource::collection($media);
    }

    /**
     * Display specific media item (if parent has access).
     */
    public function show(Media $medium)
    {
        $user = Auth::user();
        $parent = $user ? $user->parentProfile : null;

        if (!$parent) {
             return response()->json(['error' => 'Unauthorized'], 403); // أو 401 إذا كان يجب أن يكون ولي أمر
        }

        // ---=== تعديل هنا لتحديد الجدول ===---
        $childIds = $parent->children()->pluck('children.child_id')->toArray();
        $childClassIds = $parent->children()->pluck('children.class_id')->unique()->filter()->toArray();
        // ---=== نهاية التعديل ===---

        $registeredEventIds = [];
        if(!empty($childIds)){
            $registeredEventIds = EventRegistration::whereIn('child_id', $childIds)
                                    ->pluck('event_id')
                                    ->unique()
                                    ->toArray();
        }

        $isGeneral = is_null($medium->associated_child_id) && is_null($medium->associated_class_id) && is_null($medium->associated_event_id);
        $isRelatedToChild = !empty($childIds) && in_array($medium->associated_child_id, $childIds);
        $isRelatedToClass = !empty($childClassIds) && in_array($medium->associated_class_id, $childClassIds);
        $isRelatedToEvent = !empty($registeredEventIds) && in_array($medium->associated_event_id, $registeredEventIds);

        if (!($isGeneral || $isRelatedToChild || $isRelatedToClass || $isRelatedToEvent)) {
             return response()->json(['error' => 'Unauthorized'], 403);
        }

        $medium->load(['uploader', 'associatedChild', 'associatedEvent', 'associatedClass']);
        return new MediaResource($medium);
    }
}