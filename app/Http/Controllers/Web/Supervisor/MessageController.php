<?php

namespace App\Http\Controllers\Web\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;       // لجلب المستخدمين (مرسل/مستقبل/قائمة للمراسلة)
use App\Models\KindergartenClass; // لتحديد فصول المشرف
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Notifications\NewMessageNotification; // لإرسال الإشعارات
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\Supervisor\StoreMessageRequest; // يجب إنشاؤه

class MessageController extends Controller
{
    /**
     * Get the classes supervised by the current user.
     */
    private function getSupervisorClassIds()
    {
        $user = Auth::user();
        // TODO: Implement supervisor class scoping correctly
        return KindergartenClass::pluck('class_id'); // Example
        // return $user->supervisorClasses()->pluck('class_id');
    }

    /**
     * Get users the supervisor can message (Parents of supervised children + Admins).
     */
    private function getMessageableUsers()
    {
        $supervisorClassIds = $this->getSupervisorClassIds();
        $currentUserId = Auth::id();

        // 1. جلب أولياء أمور الأطفال في فصول المشرف
        $parentUsers = User::where('role', 'Parent')
                           ->where('id', '!=', $currentUserId) // استثناء المشرف نفسه
                           ->whereHas('parentProfile.children', function ($q) use ($supervisorClassIds) {
                               $q->whereIn('class_id', $supervisorClassIds);
                           })
                           ->orderBy('name')
                           ->get(['id', 'name', 'role']); // تحديد الأعمدة

        // 2. جلب المدراء
        $adminUsers = User::where('role', 'Admin')
                          ->where('id', '!=', $currentUserId) // استثناء المشرف نفسه إذا كان مديرًا أيضًا
                          ->orderBy('name')
                          ->get(['id', 'name', 'role']);

        // دمج القائمتين وإزالة التكرار (احتياطي) وترتيبها
        return $adminUsers->merge($parentUsers)->unique('id')->sortBy('name');
    }


    /**
     * Display a listing of messages for the supervisor.
     */
    public function index(Request $request)
    {
        $supervisorId = Auth::id();
        $query = Message::with(['sender', 'recipient'])
                       ->where(function ($q) use ($supervisorId) {
                           $q->where('sender_id', $supervisorId)
                             ->orWhere('recipient_id', $supervisorId);
                       });

        // --- الفلترة ---
        $searchTerm = $request->query('search');
        if ($searchTerm) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('subject', 'like', "%{$searchTerm}%")
                  ->orWhere('body', 'like', "%{$searchTerm}%");
            });
        }
        $contactId = $request->query('contact_id'); // فلتر حسب المرسل أو المستقبل الآخر
        if ($contactId) {
             $query->where(function($q) use ($supervisorId, $contactId) {
                 $q->where(function($sub) use ($supervisorId, $contactId){ // رسائل من المشرف للشخص المحدد
                     $sub->where('sender_id', $supervisorId)->where('recipient_id', $contactId);
                 })->orWhere(function($sub) use ($supervisorId, $contactId){ // رسائل إلى المشرف من الشخص المحدد
                      $sub->where('sender_id', $contactId)->where('recipient_id', $supervisorId);
                 });
             });
        }
        $readStatus = $request->query('read_status');
        if ($readStatus === 'unread') {
             $query->where('recipient_id', $supervisorId)->whereNull('read_at');
        } elseif ($readStatus === 'read') {
             $query->where('recipient_id', $supervisorId)->whereNotNull('read_at');
        }

        // --- الترتيب والـ Pagination ---
        $messages = $query->latest('sent_at')
                          ->paginate(20)
                          ->withQueryString();

        // --- بيانات إضافية للـ View ---
        // جلب قائمة مختصرة بالمستخدمين الذين تواصل معهم المشرف للفلترة
        $contactList = User::whereIn('id', function($query) use ($supervisorId) {
                                $query->select('sender_id')->from('messages')->where('recipient_id', $supervisorId);
                            })
                           ->orWhereIn('id', function($query) use ($supervisorId) {
                                $query->select('recipient_id')->from('messages')->where('sender_id', $supervisorId);
                            })
                           ->orderBy('name')
                           ->pluck('name', 'id');


        return view('web.supervisor.messages.index', compact(
            'messages',
            'contactList',
            'searchTerm',
            'contactId',
            'readStatus'
        ));
    }

    /**
     * Show the form for creating a new message.
     */
    public function create()
    {
        // قائمة المستخدمين الذين يمكن للمشرف مراسلتهم
        $recipients = $this->getMessageableUsers();

        return view('web.supervisor.messages.create', compact('recipients'));
    }

    /**
     * Store a newly created message in storage.
     */
    public function store(StoreMessageRequest $request)
    {
        $validated = $request->validated();
        $supervisorId = Auth::id();

        // التحقق مرة أخرى من أن المستقبل مسموح به (تم التحقق المبدئي في Form Request)
        $allowedRecipients = $this->getMessageableUsers()->pluck('id');
        if (!$allowedRecipients->contains($validated['recipient_id'])) {
             return back()->with('error', 'لا يمكنك إرسال رسالة لهذا المستخدم.')->withInput();
        }

        // إنشاء الرسالة
        $message = Message::create([
            'sender_id' => $supervisorId,
            'recipient_id' => $validated['recipient_id'],
            'subject' => $validated['subject'] ?? '(بدون موضوع)',
            'body' => $validated['body'],
        ]);

        // إرسال إشعار للمستقبل
        $recipient = User::find($validated['recipient_id']);
        if ($recipient) {
            try {
                Notification::send($recipient, new NewMessageNotification($message));
            } catch (\Exception $e) {
                \Log::error("Failed to send new message notification to user {$recipient->id} from supervisor {$supervisorId}: " . $e->getMessage());
            }
        }

        return redirect()->route('supervisor.messages.index')
                         ->with('success', 'تم إرسال الرسالة بنجاح.');
    }

    /**
     * Display the specified message.
     */
    public function show(Message $message)
    {
        // التحقق من الصلاحية: هل المشرف هو المرسل أو المستقبل؟
        $supervisorId = Auth::id();
        if ($message->sender_id !== $supervisorId && $message->recipient_id !== $supervisorId) {
            abort(403, 'Unauthorized access to this message.');
        }

        // تحميل العلاقات
        $message->load(['sender', 'recipient']);

        // تحديد كمقروءة إذا كان المشرف هو المستلم ولم تقرأ بعد
        if ($message->recipient_id === $supervisorId && is_null($message->read_at)) {
            $message->update(['read_at' => now()]);
        }

        return view('web.supervisor.messages.show', compact('message'));
    }


    /**
     * Remove the specified message from storage.
     * (حذف خاص بالمشرف - قد يعني إخفاء الرسالة من صندوقه فقط)
     */
    public function destroy(Message $message)
    {
        // التحقق من الصلاحية: هل المشرف هو المرسل أو المستقبل؟
         $supervisorId = Auth::id();
        if ($message->sender_id !== $supervisorId && $message->recipient_id !== $supervisorId) {
            abort(403);
        }

        try {
            // **** منطق الحذف المقترح ****
            // بدلاً من الحذف النهائي، يمكنك إضافة أعمدة مثل:
            // `sender_deleted_at` و `recipient_deleted_at`
            // وعندما يحذف المشرف، نحدث الحقل المناسب.
            // ولا نعرض الرسالة في index إذا كان الحقل المقابل للمستخدم الحالي ليس null.

            // الحل الأبسط حاليًا: الحذف النهائي (سيحذف الرسالة للطرفين)
             $message->delete();

            // الحل البديل: تحديث حقل الحذف الخاص بالمشرف
            // if ($message->sender_id === $supervisorId) {
            //     $message->update(['sender_deleted_at' => now()]);
            // } elseif ($message->recipient_id === $supervisorId) {
            //      $message->update(['recipient_deleted_at' => now()]);
            // }

            return redirect()->route('supervisor.messages.index')
                             ->with('success', 'تم حذف الرسالة بنجاح.');
        } catch (\Exception $e) {
             \Log::error("Supervisor message deletion error for ID {$message->message_id}: " . $e->getMessage());
            return redirect()->route('supervisor.messages.index')
                             ->with('error', 'فشل حذف الرسالة.');
        }
    }

    // الدوال edit و update غير مطلوبة عادة للرسائل
}