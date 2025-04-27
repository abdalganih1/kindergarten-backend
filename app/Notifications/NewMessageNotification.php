<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Message; // استيراد نموذج الرسالة

class NewMessageNotification extends Notification // implements ShouldQueue // يمكن جعله في قائمة الانتظار لتحسين الأداء
{
    use Queueable;

    public $message; // خاصية لتخزين الرسالة

    /**
     * Create a new notification instance.
     */
    public function __construct(Message $message) // استقبال الرسالة عند إنشاء الإشعار
    {
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // تحديد القنوات التي سيرسل عبرها الإشعار
        // 'database' يخزنه في جدول notifications
        // 'mail' يرسله كبريد إلكتروني
        // يمكنك إضافة قنوات أخرى مثل 'broadcast' (لـ Pusher/WebSockets)
        return ['database']; // البدء بقناة قاعدة البيانات
    }

    /**
     * Get the mail representation of the notification.
     * (مطلوبة فقط إذا كانت قناة 'mail' مفعلة في via())
     */
    // public function toMail(object $notifiable): MailMessage
    // {
    //     $senderName = $this->message->sender->name ?? 'Someone'; // اسم المرسل
    //     $subject = $this->message->subject ?? 'New Message Received';
    //     $url = url('/messages/' . $this->message->message_id); // رابط لعرض الرسالة (يحتاج لتعريف مسار ويب)

    //     return (new MailMessage)
    //                 ->subject($subject)
    //                 ->greeting('Hello ' . $notifiable->name . ',')
    //                 ->line($senderName . ' sent you a new message.')
    //                 ->action('View Message', $url)
    //                 ->line('Thank you for using our application!');
    // }

    /**
     * Get the array representation of the notification.
     * (هذه البيانات هي التي ستُخزن في عمود data في جدول notifications إذا استخدمت قناة 'database')
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message_id' => $this->message->message_id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name ?? 'Unknown Sender', // اسم المرسل
            'subject' => $this->message->subject,
            'notification_type' => 'new_message', // نوع الإشعار لتمييزه
        ];
    }

     /**
     * Get the broadcastable representation of the notification.
     * (مطلوبة فقط إذا كانت قناة 'broadcast' مفعلة في via())
     */
    // public function toBroadcast(object $notifiable): BroadcastMessage
    // {
    //     return new BroadcastMessage([
    //         'message_id' => $this->message->message_id,
    //         'sender_name' => $this->message->sender->name ?? 'Unknown Sender',
    //         'subject' => $this->message->subject,
    //     ]);
    // }
}