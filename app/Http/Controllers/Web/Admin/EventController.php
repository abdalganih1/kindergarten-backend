<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration; // لعرض أو حذف التسجيلات المرتبطة
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // للحصول على هوية المنشئ
use Carbon\Carbon; // للتعامل مع التواريخ والأوقات
use App\Http\Requests\Admin\StoreEventRequest; // يجب إنشاؤه
use App\Http\Requests\Admin\UpdateEventRequest; // يجب إنشاؤه

class EventController extends Controller
{
    /**
     * Display a listing of the events.
     * عرض قائمة بالفعاليات مع الفلترة والبحث.
     */
    public function index(Request $request)
    {
        $query = Event::with('createdByAdmin.user') // تحميل علاقة المدير والمستخدم
                      ->withCount('registrations'); // حساب عدد التسجيلات لكل فعالية

        // --- الفلترة والبحث ---
        // 1. البحث عن اسم الفعالية أو الموقع
        $searchTerm = $request->query('search');
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('event_name', 'like', "%{$searchTerm}%")
                  ->orWhere('location', 'like', "%{$searchTerm}%");
            });
        }

        // 2. الفلترة حسب حالة الفعالية (قادمة / منتهية)
        $status = $request->query('status', 'upcoming'); // الافتراضي: قادمة
        if ($status === 'upcoming') {
            $query->where('event_date', '>=', now());
        } elseif ($status === 'past') {
            $query->where('event_date', '<', now());
        }
        // إذا كانت القيمة 'all'، لا يتم تطبيق فلتر التاريخ

        // --- الترتيب والـ Pagination ---
        $orderBy = ($status === 'past') ? 'desc' : 'asc'; // ترتيب القادمة تصاعديًا والمنتهية تنازليًا
        $events = $query->orderBy('event_date', $orderBy)
                        ->paginate(15)
                        ->withQueryString();

        // إرسال البيانات للواجهة
        return view('web.admin.events.index', compact('events', 'searchTerm', 'status'));
    }

    /**
     * Show the form for creating a new event.
     * عرض نموذج إضافة فعالية جديدة.
     */
    public function create()
    {
        return view('web.admin.events.create');
    }

    /**
     * Store a newly created event in storage.
     * تخزين الفعالية الجديدة.
     *
     * @param  \App\Http\Requests\Admin\StoreEventRequest  $request
     */
    public function store(StoreEventRequest $request)
    {
        $validated = $request->validated();
        $admin = Auth::user()->adminProfile;

        if (!$admin) {
            return back()->with('error', 'Admin profile not found.')->withInput();
        }

        Event::create([
            'event_name' => $validated['event_name'],
            'description' => $validated['description'] ?? null,
            'event_date' => $validated['event_date'], // يتم التحقق من التنسيق في Form Request
            'location' => $validated['location'] ?? null,
            'requires_registration' => $validated['requires_registration'] ?? false, // الحصول على القيمة البوليانية
            'registration_deadline' => ($validated['requires_registration'] ?? false) ? ($validated['registration_deadline'] ?? null) : null, // تعيين الموعد النهائي فقط إذا كان التسجيل مطلوبًا
            'created_by_id' => $admin->admin_id,
        ]);

        return redirect()->route('admin.events.index')
                         ->with('success', 'تم إنشاء الفعالية بنجاح.');
    }

    /**
     * Display the specified event and its registrations.
     * عرض تفاصيل فعالية محددة وقائمة الأطفال المسجلين بها.
     *
     * @param  \App\Models\Event  $event // استخدام Route Model Binding
     */
    public function show(Event $event)
    {
        // تحميل العلاقات اللازمة
        $event->load(['createdByAdmin.user', 'registrations.child.kindergartenClass']); // تحميل المدير والمستخدم والتسجيلات والطفل والفصل

        // جلب التسجيلات بشكل منفصل للتحكم في الـ pagination الخاص بها
        $registrations = $event->registrations()
                               ->with(['child.kindergartenClass']) // تأكيد تحميل الطفل والفصل
                               ->paginate(20, ['*'], 'regs_page'); // استخدام paginate مع اسم صفحة مخصص

        return view('web.admin.events.show', compact('event', 'registrations'));
    }

    /**
     * Show the form for editing the specified event.
     * عرض نموذج تعديل فعالية موجودة.
     *
     * @param  \App\Models\Event  $event
     */
    public function edit(Event $event)
    {
        // التأكد من تنسيق التواريخ لعرضها بشكل صحيح في حقول النموذج
        if ($event->event_date instanceof Carbon) {
            $event->event_date_form = $event->event_date->format('Y-m-d\TH:i');
        }
         if ($event->registration_deadline instanceof Carbon) {
            $event->registration_deadline_form = $event->registration_deadline->format('Y-m-d\TH:i');
        } elseif ($event->registration_deadline) { // إذا كانت سلسلة نصية
             try { $event->registration_deadline_form = Carbon::parse($event->registration_deadline)->format('Y-m-d\TH:i'); } catch (\Exception $e) {}
        }


        return view('web.admin.events.edit', compact('event'));
    }

    /**
     * Update the specified event in storage.
     * تحديث بيانات الفعالية.
     *
     * @param  \App\Http\Requests\Admin\UpdateEventRequest  $request
     * @param  \App\Models\Event  $event
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        $validated = $request->validated();

        $requiresRegistration = $validated['requires_registration'] ?? $event->requires_registration; // استخدم القيمة القديمة إذا لم يتم إرسال الجديدة

        // تحديث الفعالية
        $event->update([
            'event_name' => $validated['event_name'],
            'description' => $validated['description'] ?? null,
            'event_date' => $validated['event_date'],
            'location' => $validated['location'] ?? null,
            'requires_registration' => $requiresRegistration,
            // تحديث الموعد النهائي فقط إذا كان التسجيل مطلوبًا
            'registration_deadline' => $requiresRegistration ? ($validated['registration_deadline'] ?? null) : null,
        ]);

         return redirect()->route('admin.events.index')->with('success', 'تم تحديث الفعالية بنجاح.');
         // أو يمكنك إعادة التوجيه إلى صفحة عرض الفعالية:
         // return redirect()->route('admin.events.show', $event)->with('success', 'تم تحديث الفعالية بنجاح.');
    }

    /**
     * Remove the specified event from storage.
     * حذف الفعالية (والتسجيلات المرتبطة بها بسبب cascade on delete المتوقع).
     *
     * @param  \App\Models\Event  $event
     */
    public function destroy(Event $event)
    {
        try {
            // حذف الفعالية (سيتم حذف التسجيلات المرتبطة بها إذا تم تعريف ON DELETE CASCADE)
            // تأكد من تعريف ذلك في ملف الهجرة الخاص بـ event_registrations
             $event->delete();
             return redirect()->route('admin.events.index')
                             ->with('success', 'تم حذف الفعالية بنجاح.');
        } catch (\Illuminate\Database\QueryException $e) {
            // التعامل مع أخطاء المفتاح الأجنبي إذا لم يتم حذف التسجيلات تلقائيًا
             return redirect()->route('admin.events.index')
                             ->with('error', 'لا يمكن حذف الفعالية لوجود تسجيلات مرتبطة بها. يرجى حذف التسجيلات أولاً أو التحقق من قيود قاعدة البيانات.');
        } catch (\Exception $e) {
             return redirect()->route('admin.events.index')
                             ->with('error', 'فشل حذف الفعالية. يرجى المحاولة مرة أخرى.');
        }
    }
}