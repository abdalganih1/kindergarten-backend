<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User; // To find recipients
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\MessageResource;
use Illuminate\Validation\Rule;
use App\Notifications\NewMessageNotification; // <-- استيراد الإشعار (سننشئه)
use Illuminate\Support\Facades\Notification; // <-- استيراد Notification facade

class MessageController extends Controller
{
    /**
     * Display messages for the authenticated user (sent or received) with pagination.
     * Currently lists latest messages, UI can group them.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request) // <-- إضافة Request $request
    {
        $userId = Auth::id();

        // --- تنفيذ TODO: Pagination ---
        $perPage = $request->query('per_page', 20); // عدد الرسائل لكل صفحة

        // --- تنفيذ TODO: Grouping (الطريقة الأبسط: جلب الأحدث) ---
        $messages = Message::with(['sender', 'recipient']) // تحميل العلاقات
                    ->where(function ($query) use ($userId) { // تجميع شروط Where
                        $query->where('sender_id', $userId)
                              ->orWhere('recipient_id', $userId);
                    })
                    ->latest('sent_at') // الترتيب حسب الأحدث
                    ->paginate($perPage); // تطبيق الـ pagination

        // إرجاع النتائج
        return MessageResource::collection($messages);
    }

    /**
     * Store a new message sent by the authenticated user and notify recipient.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\MessageResource|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
         $validated = $request->validate([
            'recipient_id' => [
                'required',
                'integer',
                'different:sender_id', // التأكد من أن المرسل والمستقبل مختلفان (sender_id غير موجود مباشرة في الطلب، لكن يمكن إضافته أو التحقق منه لاحقًا)
                Rule::exists('users', 'id')->whereIn('role', ['Admin', 'Supervisor']), // السماح بالإرسال للمدراء والمشرفين فقط
            ],
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string|max:5000',
        ]);

         // منع إرسال الرسالة لنفس المستخدم
        if ($validated['recipient_id'] == Auth::id()) {
             return response()->json(['error' => 'Cannot send message to yourself.'], 422);
        }

        // إنشاء الرسالة
        $message = Message::create([
            'sender_id' => Auth::id(),
            'recipient_id' => $validated['recipient_id'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
        ]);

        // --- تنفيذ TODO: إرسال إشعار للمستقبل ---
        $recipient = User::find($validated['recipient_id']);
        if ($recipient) {
            try {
                 // استخدام Notification facade لإرسال الإشعار
                 // سيتم إرسال الإشعار عبر القنوات المحددة في نموذج User (مثل 'database')
                Notification::send($recipient, new NewMessageNotification($message));
                // أو يمكن استخدام: $recipient->notify(new NewMessageNotification($message));
            } catch (\Exception $e) {
                // تسجيل الخطأ إذا فشل إرسال الإشعار، لكن لا توقف العملية
                \Log::error("Failed to send new message notification to user {$recipient->id}: " . $e->getMessage());
            }
        }
        // ------------------------------------------

        // إرجاع بيانات الرسالة الجديدة
        return new MessageResource($message->load(['sender', 'recipient']));
    }

    /**
     * Display the specified message (if user is sender or recipient).
     *
     * @param  \App\Models\Message  $message
     * @return \App\Http\Resources\MessageResource|\Illuminate\Http\JsonResponse
     */
    public function show(Message $message)
    {
        // التحقق من الصلاحية
        if ($message->sender_id !== Auth::id() && $message->recipient_id !== Auth::id()) {
             return response()->json(['error' => 'Unauthorized'], 403);
        }

        // تحديد الرسالة كمقروءة إذا كان المستخدم الحالي هو المستقبل ولم تُقرأ بعد
        if ($message->recipient_id === Auth::id() && is_null($message->read_at)) {
            $message->update(['read_at' => now()]);
        }

        // تحميل العلاقات قبل إرسالها للريسورس
        $message->load(['sender', 'recipient']);
        return new MessageResource($message);
    }

    // update, destroy - maybe allow user to delete their messages?
    // يمكن إضافة دالة destroy للسماح للمستخدم بحذف رسائله (قد تكون حذفًا ناعمًا أو علاقة منفصلة لتتبع الحذف لكل مستخدم)
    /**
     * Remove the specified message (soft delete maybe?).
     * Needs careful consideration on how deletion works (for sender? recipient? both?)
     */
    // public function destroy(Message $message)
    // {
    //     // Authorization: Ensure user is sender or recipient
    //     if ($message->sender_id !== Auth::id() && $message->recipient_id !== Auth::id()) {
    //          return response()->json(['error' => 'Unauthorized'], 403);
    //     }

    //     // Implement deletion logic (e.g., add sender_deleted_at, recipient_deleted_at columns)
    //     // Or simply delete if acceptable
    //     // $message->delete();

    //     return response()->json(['message' => 'Message deleted successfully.'], 200);
    // }
}