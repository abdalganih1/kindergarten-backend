<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KindergartenClass extends Model
{
    use HasFactory;

    protected $table = 'kindergarten_classes';
    protected $primaryKey = 'class_id';

    protected $fillable = [
        'class_name',
        'description',
        'min_age',
        'max_age',
    ];

    // Define relationships (e.g., to Children, WeeklySchedule, DailyMeals, Media, Announcements)
    public function children()
    {
        return $this->hasMany(Child::class, 'class_id', 'class_id');
    }

    public function weeklySchedules()
    {
        return $this->hasMany(WeeklySchedule::class, 'class_id', 'class_id');
    }
    public function supervisors(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
{
     // تأكد من استخدام اسم الجدول الوسيط الصحيح وأسماء الأعمدة
     return $this->belongsToMany(User::class, 'supervisor_classes', 'class_id', 'user_id')
                 ->where('role', 'Supervisor'); // فلترة لجلب المشرفين فقط
}
}