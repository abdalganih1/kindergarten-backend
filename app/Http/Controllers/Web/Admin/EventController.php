<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration; // لعرض أو حذف التسجيلات المرتبطة
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // للحصول على هوية المنشئ (المستخدم الحالي)
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
        // تم تعديل العلاقة من createdByAdmin.user إلى creator (وهو كائن User مباشرة)
        $query = Event::with('creator') // تحميل علاقة منشئ الفعالية (كائن User)
                      ->withCount('registrations'); // حساب عدد التسجيلات لكل فعالية

        // --- الفلترة والبحث ---
        $searchTerm = $request->query('search');
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('event_name', 'like', "%{$searchTerm}%")
                  ->orWhere('location', 'like', "%{$searchTerm}%");
            });
        }

        $status = $request->query('status', 'upcoming');
        if ($status === 'upcoming') {
            $query->where('event_date', '>=', now());
        } elseif ($status === 'past') {
            $query->where('event_date', '<', now());
        }

        // --- الترتيب والـ Pagination ---
        $orderBy = ($status === 'past') ? 'desc' : 'asc';
        $events = $query->orderBy('event_date', $orderBy)
                        ->paginate(15)
                        ->withQueryString();

        return view('web.admin.events.index', compact('events', 'searchTerm', 'status'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create()
    {
        return view('web.admin.events.create');
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(StoreEventRequest $request)
    {
        $validated = $request->validated();
        // $admin = Auth::user()->adminProfile; // لم نعد نستخدم adminProfile للعلاقة
        $creatorId = Auth::id(); // نستخدم ID المستخدم الحالي مباشرة

        // if (!$admin) { // لم يعد هذا الشرط ضروريًا بهذه الطريقة
        //     return back()->with('error', 'Admin profile not found.')->withInput();
        // }

        Event::create([
            'event_name' => $validated['event_name'],
            'description' => $validated['description'] ?? null,
            'event_date' => $validated['event_date'],
            'location' => $validated['location'] ?? null,
            'requires_registration' => $validated['requires_registration'] ?? false,
            'registration_deadline' => ($validated['requires_registration'] ?? false) ? ($validated['registration_deadline'] ?? null) : null,
            'created_by_id' => $creatorId, // تم التعديل هنا
        ]);

        return redirect()->route('admin.events.index')
                         ->with('success', 'تم إنشاء الفعالية بنجاح.');
    }

    /**
     * Display the specified event and its registrations.
     */
    public function show(Event $event)
    {
        // تم تعديل تحميل العلاقة
        $event->load(['creator', 'registrations.child.kindergartenClass']); // تحميل منشئ الفعالية (User)

        $registrations = $event->registrations()
                               ->with(['child.kindergartenClass'])
                               ->paginate(20, ['*'], 'regs_page');

        return view('web.admin.events.show', compact('event', 'registrations'));
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event)
    {
        // التأكد من تنسيق التواريخ لعرضها بشكل صحيح في حقول النموذج
        // (لا تغيير هنا، فقط للتأكيد أن $event->event_date و $event->registration_deadline يتم استخدامها)
        if ($event->event_date instanceof Carbon) {
            $event->event_date_form = $event->event_date->format('Y-m-d\TH:i');
        } elseif ($event->event_date) {
             try { $event->event_date_form = Carbon::parse($event->event_date)->format('Y-m-d\TH:i'); } catch (\Exception $e) { $event->event_date_form = null;}
        } else {
            $event->event_date_form = null;
        }

         if ($event->registration_deadline instanceof Carbon) {
            $event->registration_deadline_form = $event->registration_deadline->format('Y-m-d\TH:i');
        } elseif ($event->registration_deadline) {
             try { $event->registration_deadline_form = Carbon::parse($event->registration_deadline)->format('Y-m-d\TH:i'); } catch (\Exception $e) { $event->registration_deadline_form = null;}
        } else {
            $event->registration_deadline_form = null;
        }


        return view('web.admin.events.edit', compact('event'));
    }

    /**
     * Update the specified event in storage.
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        $validated = $request->validated();

        // التحضير للـ boolean
        $requiresRegistration = $request->has('requires_registration') ? $request->boolean('requires_registration') : $event->requires_registration;


        $event->update([
            'event_name' => $validated['event_name'],
            'description' => $validated['description'] ?? null,
            'event_date' => $validated['event_date'],
            'location' => $validated['location'] ?? null,
            'requires_registration' => $requiresRegistration,
            'registration_deadline' => $requiresRegistration ? ($validated['registration_deadline'] ?? null) : null,
            // created_by_id لا يتم تحديثه عادةً
        ]);

         return redirect()->route('admin.events.index')->with('success', 'تم تحديث الفعالية بنجاح.');
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(Event $event)
    {
        try {
             $event->delete();
             return redirect()->route('admin.events.index')
                             ->with('success', 'تم حذف الفعالية بنجاح.');
        } catch (\Illuminate\Database\QueryException $e) {
             return redirect()->route('admin.events.index')
                             ->with('error', 'لا يمكن حذف الفعالية لوجود تسجيلات مرتبطة بها. يرجى حذف التسجيلات أولاً أو التحقق من قيود قاعدة البيانات.');
        } catch (\Exception $e) {
             return redirect()->route('admin.events.index')
                             ->with('error', 'فشل حذف الفعالية. يرجى المحاولة مرة أخرى.');
        }
    }
}