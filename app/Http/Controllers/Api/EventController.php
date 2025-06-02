<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\EventResource;
use App\Models\ParentModel; // <-- استيراد ParentModel (أو Parent)

class EventController extends Controller
{
    /**
     * Display a listing of upcoming/active events with pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);

        // جلب الفعاليات مع تحميل علاقة المدير (الذي أنشأها)
        // لاحظ أن createdByAdmin يشير إلى نموذج Admin، يجب تعديله ليشير لـ User
        $events = Event::with('creator') // تم تغيير createdByAdmin إلى creator (علاقة مع User)
                 ->where('event_date', '>=', now())
                 ->orderBy('event_date', 'asc')
                 ->paginate($perPage);

        return EventResource::collection($events);
    }

    /**
     * Display the specified event, indicating if parent's children are registered.
     *
     * @param  \App\Models\Event  $event
     * @return \App\Http\Resources\EventResource|\Illuminate\Http\JsonResponse
     */
    public function show(Event $event)
    {
        $user = Auth::user();
        $parent = $user ? $user->parentProfile : null; // افتراض أن علاقة parentProfile موجودة في User

        // ---=== تعديل هنا لتحديد الجدول ===---
        $childIds = collect(); // قيمة افتراضية
        if ($parent) {
            // نحدد أننا نريد child_id من جدول children
            $childIds = $parent->children()->pluck('children.child_id');
        }
        // ---=== نهاية التعديل ===---


        // تحميل العلاقات الضرورية
        // تأكد من أن علاقة creator موجودة في نموذج Event وتشير إلى User
        $event->load(['creator', 'registrations.child']);

        $isAnyChildRegistered = false;
        if ($parent && !$childIds->isEmpty()) {
             $isAnyChildRegistered = $event->registrations()
                                          ->whereIn('child_id', $childIds->all()) // استخدام all() لتحويل Collection إلى array
                                          ->exists();
        }

        // تمرير المعلومات الإضافية إلى الريسورس
        return (new EventResource($event))->additional([
            'meta' => [
                'is_registered_by_current_user' => $isAnyChildRegistered,
            ]
        ]);
    }
}