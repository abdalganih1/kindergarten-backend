<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Observation extends Model
{
    use HasFactory;

    protected $primaryKey = 'observation_id';

    protected $fillable = [
        'parent_id',
        'child_id',
        'observation_text',
        'submitted_at', // يتم تعيينه افتراضيًا في الهجرة، لكن جيد أن يكون هنا للوضوح
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'submitted_at' => 'datetime', // <-- أضف هذا السطر
        // created_at و updated_at غير موجودين افتراضيًا بهذا الاسم،
        // ولكن إذا أضفتهم بـ timestamps()، فسيتم تحويلهم تلقائيًا.
    ];

    // --- العلاقات ---
    public function parentSubmitter()
    {
        return $this->belongsTo(ParentModel::class, 'parent_id', 'parent_id');
    }

    public function child()
    {
        return $this->belongsTo(Child::class, 'child_id', 'child_id');
    }

    // --- تعريف عدم استخدام timestamps إذا لم تكن موجودة ---
    // إذا كان جدول observations لا يحتوي على created_at و updated_at
    // public $timestamps = false;

    // --- تعريف أسماء مخصصة (غير مرجح هنا) ---
    // const CREATED_AT = 'submitted_at'; // لا تفعل هذا عادةً
    // const UPDATED_AT = null; // تعطيل updated_at
}