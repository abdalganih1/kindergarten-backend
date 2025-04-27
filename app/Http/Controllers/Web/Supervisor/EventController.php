<?php

namespace App\Http\Controllers\Web\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the events.
     */
    public function index(Request $request)
    {
        // نفس منطق عرض الفعاليات الخاص بالمدير (لأن المشرف يعرض الكل)
        $query = Event::with('createdByAdmin.user')->withCount('registrations');

        $searchTerm = $request->query('search');
        if ($searchTerm) { /* ... بحث ... */ }
        $status = $request->query('status', 'upcoming');
        if ($status === 'upcoming') { $query->where('event_date', '>=', now()); }
        elseif ($status === 'past') { $query->where('event_date', '<', now()); }

        $orderBy = ($status === 'past') ? 'desc' : 'asc';
        $events = $query->orderBy('event_date', $orderBy)
                        ->paginate(15)->withQueryString();

        return view('web.supervisor.events.index', compact('events', 'searchTerm', 'status'));
    }

    /**
     * Display the specified event and its registrations.
     */
    public function show(Event $event)
    {
        // نفس منطق عرض تفاصيل الفعالية الخاص بالمدير
        $event->load(['createdByAdmin.user', 'registrations.child.kindergartenClass']);
        $registrations = $event->registrations()
                               ->with(['child.kindergartenClass'])
                               ->paginate(20, ['*'], 'regs_page');

        return view('web.supervisor.events.show', compact('event', 'registrations'));
    }

    // لا يوجد دوال أخرى للمشرف
}