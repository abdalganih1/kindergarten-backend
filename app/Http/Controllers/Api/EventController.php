<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\EventResource;

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
        // --- تنفيذ TODO: Add pagination ---
        $perPage = $request->query('per_page', 10); // عدد الفعاليات لكل صفحة، افتراضي 10

        // Fetch upcoming/active events
        $events = Event::with('createdByAdmin') // تحميل علاقة المدير
                 ->where('event_date', '>=', now()) // عرض الفعاليات المستقبلية والحالية فقط
                 ->orderBy('event_date', 'asc') // ترتيب حسب تاريخ الفعالية الأقرب أولاً
                 ->paginate($perPage); // استخدام paginate بدلاً من get

        // Note: Checking registration status for *all* events in index can be inefficient.
        // It's generally better to check upon viewing a specific event (show method)
        // or potentially return a separate list of events the user *is* registered for.

        // Laravel's Resource Collection handles pagination meta/links automatically
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
        // تأكد من وجود ملف ولي الأمر قبل استخدامه
        $parent = $user ? $user->parentProfile : null;
        $childIds = $parent ? $parent->children()->pluck('child_id') : collect();

        // تحميل العلاقات الضرورية
        $event->load(['createdByAdmin', 'registrations.child']); // Load registrations and the child

        // التحقق مما إذا كان أي من أطفال ولي الأمر مسجلين في هذه الفعالية
        $isAnyChildRegistered = false;
        // تأكد من أن $childIds ليست فارغة قبل استخدامها في الاستعلام
        if ($parent && !$childIds->isEmpty()) {
             $isAnyChildRegistered = $event->registrations()
                                          ->whereIn('child_id', $childIds)
                                          ->exists();
        }

        // --- طريقة أفضل لتمرير المعلومات الإضافية إلى الريسورس ---
        // بدلاً من إضافة خاصية ديناميكية، يمكن تمريرها إلى الريسورس

        // return new EventResource($event); // الطريقة القديمة

        // الطريقة الأفضل: استخدام additional للمعلومات السياقية
        return (new EventResource($event))->additional([
            'meta' => [
                'is_registered_by_current_user' => $isAnyChildRegistered,
            ]
        ]);

        // أو تعديل EventResource ليشمل هذه القيمة إذا كانت مطلوبة دائمًا
        // في EventResource.php:
        // 'is_registered' => $this->when(isset($this->is_registered_by_current_user), $this->is_registered_by_current_user),
        // وتحتاج لتمرير القيمة إلى المودل قبل إنشاء الريسورس
    }

     // store, update, destroy not needed for parents
}