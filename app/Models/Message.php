<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $primaryKey = 'message_id';

    // Laravel يحمي created_at و updated_at افتراضيًا
    // ولكن إذا كنت لا تستخدمهما وتريد السماح بـ sent_at، أضفه لـ fillable
    protected $fillable = [
        'sender_id',
        'recipient_id',
        'subject',
        'body',
        'sent_at', // تأكد من وجوده إذا كنت تعينه يدويًا أحيانًا
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sent_at' => 'datetime', // <-- أضف هذا
        'read_at' => 'datetime', // <-- أضف هذا
        // created_at و updated_at يتم تحويلهما تلقائيًا إذا كانا موجودين
    ];

    // --- العلاقات ---
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id', 'id');
    }

    // --- مهم: تعريف Laravel بعدم استخدام created_at/updated_at إذا لم يكونا في جدولك ---
    // إذا كان جدول messages لا يحتوي على عمودي created_at و updated_at
    // يجب إضافة السطر التالي لمنع Eloquent من محاولة تعبئتهما تلقائيًا:
    // public $timestamps = false;

    // --- مهم: تعريف أسماء أعمدة timestamps مخصصة إذا استخدمتها ---
    // إذا استخدمت sent_at كـ created_at و read_at كـ updated_at (وهو أمر غير شائع وغير منطقي هنا)
    // const CREATED_AT = 'sent_at';
    // const UPDATED_AT = 'read_at'; // هذا غير صحيح منطقيًا لـ read_at
}