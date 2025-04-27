<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User; // للفلترة واختيار المستلمين
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // لاستخدام Rule::in
use App\Notifications\NewMessageNotification; // لإرسال الإشعارات
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\Admin\StoreMessageRequest; // <-- سننشئ هذا

class MessageController extends Controller
{
    /**
     * Display a listing of messages (sent and received by admin/system).
     */
    public function index(Request $request)
    {
        // ... (كود index يبقى كما هو في ردك السابق) ...
        $query = Message::with(['sender', 'recipient']);

        $searchTerm = $request->query('search');
        if ($searchTerm) { /* ... */ }
        $senderId = $request->query('sender_id');
        if ($senderId) { $query->where('sender_id', $senderId); }
        $recipientId = $request->query('recipient_id');
        if ($recipientId) { $query->where('recipient_id', $recipientId); }
        $readStatus = $request->query('read_status');
        if ($readStatus === 'read') { $query->where('recipient_id', Auth::id())->whereNotNull('read_at'); }
        elseif ($readStatus === 'unread') { $query->where('recipient_id', Auth::id())->whereNull('read_at'); }

        $messages = $query->latest('sent_at')->paginate(20)->withQueryString();
        $users = User::orderBy('name')->pluck('name', 'id');

        return view('web.admin.messages.index', compact( 'messages', 'users', 'searchTerm', 'senderId', 'recipientId', 'readStatus' ));
    }

    /**
     * Show the form for creating a new message.
     * عرض نموذج إنشاء رسالة جديدة.
     */
    public function create(Request $request) // إضافة Request لجلب بيانات الرد
    {
        // جلب كل المستخدمين (أو يمكنك فلترتهم إذا أردت) ما عدا المدير الحالي
        $recipients = User::where('id', '!=', Auth::id())
                          ->orderBy('role')->orderBy('name') // ترتيب حسب الدور ثم الاسم
                          ->get(['id', 'name', 'role']);

        // بيانات الرد (إذا جاء من زر الرد)
        $replyToUser = null;
        $originalSubject = '';
        if ($request->has('reply_to')) {
            $replyToUser = User::find($request->input('reply_to'));
            $originalSubject = $request->input('subject', '');
            // إضافة 'Re:' إذا لم تكن موجودة بالفعل
            if ($originalSubject && !str_starts_with(strtolower($originalSubject), 're:')) {
                $originalSubject = 'Re: ' . $originalSubject;
            }
        }

        return view('web.admin.messages.create', compact('recipients', 'replyToUser', 'originalSubject'));
    }

    /**
     * Store a newly created message in storage.
     * تخزين الرسالة الجديدة (أو الرد).
     *
     * @param  \App\Http\Requests\Admin\StoreMessageRequest  $request
     */
    public function store(StoreMessageRequest $request)
    {
        $validated = $request->validated();
        $adminUserId = Auth::id();

        // منع إرسال الرسالة لنفس المستخدم
        if ($validated['recipient_id'] == $adminUserId) {
             return back()->with('error', 'لا يمكنك إرسال رسالة لنفسك.')->withInput();
        }

        // إنشاء الرسالة
        $message = Message::create([
            'sender_id' => $adminUserId,
            'recipient_id' => $validated['recipient_id'],
            'subject' => $validated['subject'] ?? '(بدون موضوع)',
            'body' => $validated['body'],
            // sent_at يتم تعيينها بواسطة timestamps أو يمكن تعيينها يدويًا إذا أردت
            // 'sent_at' => now(),
        ]);

        // إرسال إشعار للمستقبل
        $recipient = User::find($validated['recipient_id']);
        if ($recipient) {
            try {
                Notification::send($recipient, new NewMessageNotification($message));
            } catch (\Exception $e) {
                \Log::error("Admin: Failed to send new message notification to user {$recipient->id}: " . $e->getMessage());
            }
        }

        return redirect()->route('admin.messages.index')->with('success', 'تم إرسال الرسالة بنجاح.');
    }

    /**
     * Display the specified message and potentially a reply form.
     */
    public function show(Message $message)
    {
        $message->load(['sender.adminProfile', 'sender.parentProfile', 'recipient.adminProfile', 'recipient.parentProfile']);

        // تحديد كمقروءة إذا كان المدير الحالي هو المستلم ولم تقرأ بعد
        if ($message->recipient_id === Auth::id() && is_null($message->read_at)) {
            $message->update(['read_at' => now()]);
        }

        // تحديد المستلم للرد (هو مرسل الرسالة الأصلية)
        $replyRecipient = $message->sender;
        // تحديد الموضوع للرد
        $replySubject = $message->subject ?? '';
        if ($replySubject && !str_starts_with(strtolower($replySubject), 're:')) {
            $replySubject = 'Re: ' . $replySubject;
        }

        return view('web.admin.messages.show', compact('message', 'replyRecipient', 'replySubject'));
    }


    /**
     * Remove the specified message from storage.
     */
    public function destroy(Message $message)
    {
        // يمكن للمدير حذف أي رسالة
        try {
            $message->delete();
            return redirect()->route('admin.messages.index')
                             ->with('success', 'تم حذف الرسالة بنجاح.');
        } catch (\Exception $e) {
             \Log::error("Admin message deletion error for ID {$message->message_id}: " . $e->getMessage());
            return redirect()->route('admin.messages.index')
                             ->with('error', 'فشل حذف الرسالة.');
        }
    }

    // الدوال edit و update غير مطلوبة
}