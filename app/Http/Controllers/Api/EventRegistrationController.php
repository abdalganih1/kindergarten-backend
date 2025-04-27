<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventRegistration;
use App\Models\Event;
use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Http\Resources\EventRegistrationResource;

class EventRegistrationController extends Controller
{
    /**
     * Display registrations for the authenticated parent's children with pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request) // إضافة Request $request
    {
        $user = Auth::user();
        $parent = $user ? $user->parentProfile : null;

        if (!$parent) {
             return response()->json(['error' => 'Parent profile not found.'], 404);
        }

        // الحصول على معرفات أطفال ولي الأمر
        $childIds = $parent->children()->pluck('child_id');

        // إذا لم يكن لدى ولي الأمر أطفال، أرجع مجموعة فارغة
        if ($childIds->isEmpty()) {
            return EventRegistrationResource::collection(collect()); // إرجاع مجموعة فارغة لـ pagination
        }

        // --- تنفيذ TODO: Pagination ---
        $perPage = $request->query('per_page', 10); // عدد التسجيلات لكل صفحة، افتراضي 10

        // جلب التسجيلات مع تحميل العلاقات وترتيبها وتطبيق الـ pagination
        $registrations = EventRegistration::with(['event', 'child']) // تحميل العلاقات الضرورية
                           ->whereIn('child_id', $childIds) // فقط تسجيلات أطفال ولي الأمر
                           ->latest('registration_date') // الترتيب حسب الأحدث
                           ->paginate($perPage); // تطبيق الـ pagination

        // إرجاع النتائج باستخدام الريسورس (الذي سيتضمن بيانات الـ pagination تلقائيًا)
        return EventRegistrationResource::collection($registrations);
    }

    /**
     * Register a specific child (owned by parent) for an event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Event  $event
     * @return \App\Http\Resources\EventRegistrationResource|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Event $event)
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
            'child_id' => [
                'required',
                'integer',
                // التأكد من وجود الطفل وأنه ينتمي لولي الأمر المسجل دخوله
                Rule::exists('children', 'child_id')->where(function ($query) use ($parentChildIds) {
                    // يجب أن يكون معرف الطفل ضمن معرفات أطفال ولي الأمر
                    $query->whereIn('child_id', $parentChildIds);
                }),
            ],
            'parent_consent' => 'sometimes|boolean', // الموافقة اختيارية
        ]);

        // التحقق من شروط الفعالية (تتطلب تسجيل؟، الموعد النهائي؟)
        if (!$event->requires_registration) {
             return response()->json(['error' => 'This event does not require registration.'], 400);
        }
        if ($event->registration_deadline && now()->gt($event->registration_deadline)) {
             return response()->json(['error' => 'Registration deadline has passed.'], 400);
        }

        // التحقق مما إذا كان الطفل مسجلًا بالفعل في هذه الفعالية
        $existing = EventRegistration::where('event_id', $event->event_id)
                                     ->where('child_id', $validated['child_id'])
                                     ->first();
        if ($existing) {
            // إرجاع خطأ يفيد بأن الطفل مسجل بالفعل
            return response()->json(['error' => 'Child already registered for this event.'], 409); // 409 Conflict
        }

        // إنشاء سجل التسجيل
        $registration = EventRegistration::create([
            'event_id' => $event->event_id,
            'child_id' => $validated['child_id'],
            'parent_consent' => $request->input('parent_consent', false),
            // registration_date لديها قيمة افتراضية CURRENT_TIMESTAMP
        ]);

        // إرجاع بيانات التسجيل الجديد باستخدام الريسورس
        return new EventRegistrationResource($registration->load(['event', 'child']));
    }

    /**
     * Cancel a registration (unregister child).
     *
     * @param  \App\Models\EventRegistration  $registration
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(EventRegistration $registration) // استخدام Route Model Binding
    {
        $user = Auth::user();
        $parent = $user ? $user->parentProfile : null;

        if (!$parent) {
             return response()->json(['error' => 'Parent profile not found.'], 404);
        }

        // التحقق من الصلاحية: هل التسجيل يخص أحد أطفال ولي الأمر؟
        if (!$parent->children()->where('child_id', $registration->child_id)->exists()) {
             return response()->json(['error' => 'Unauthorized'], 403);
        }

        // --- تنفيذ TODO (اختياري): التحقق مما إذا كان الإلغاء مسموحًا به ---
        // مثال: عدم السماح بالإلغاء بعد بدء الفعالية
        // if (now()->gt($registration->event->event_date)) {
        //     return response()->json(['error' => 'Cannot cancel registration after the event has started.'], 400);
        // }
        // --------------------------------------------------------------

        // حذف التسجيل
        $registration->delete();

        // إرجاع رسالة نجاح
        return response()->json(['message' => 'Registration cancelled successfully.'], 200);
    }

    // show, update not likely needed for parents via API
}