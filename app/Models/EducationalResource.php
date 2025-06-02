<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationalResource extends Model
{
    use HasFactory;

    protected $primaryKey = 'resource_id';

    // هذا يخبر Eloquent أن "تاريخ الإنشاء" هو added_at
    // لكن تذكر أن العمود الفعلي في قاعدة البيانات يجب أن يكون created_at إذا كنت تستخدم timestamps()
    // إذا كان العمود الفعلي في قاعدة البيانات هو added_at، فهذا صحيح
    // const CREATED_AT = 'added_at'; // <-- إذا كان عمود قاعدة البيانات هو added_at
    // const UPDATED_AT = 'updated_at'; // إذا كان لديك عمود updated_at مختلف

    protected $fillable = [
        'title',
        'description',
        'resource_type',
        'url_or_path',
        'target_age_min',
        'target_age_max',
        'subject',
        'added_by_id',
        // 'added_at' // لا تضعه هنا إذا كان Laravel سيديره كـ created_at
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        // إذا كان اسم العمود في قاعدة البيانات هو 'created_at' لكنك تريد الوصول إليه كـ 'added_at'
        // فهذا لا يتم عبر $casts. $casts لتحويل الأنواع.
        // إذا كان العمود الفعلي 'added_at' وتريد تحويله لـ datetime:
        // 'added_at' => 'datetime',
    ];


    // ---=== تعديل اسم العلاقة هنا وفي المتحكم ===---
    /**
     * Get the user who added the resource.
     */
    public function addedByUser() // تم تغيير الاسم ليكون أوضح أنه User
    {
        // يفترض أن added_by_id هو user_id
        return $this->belongsTo(User::class, 'added_by_id', 'id');
    }
    // ---=== نهاية التعديل ===---
}