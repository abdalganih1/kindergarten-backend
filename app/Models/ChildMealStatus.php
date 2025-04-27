<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // لاستخدام علاقات BelongsTo

class ChildMealStatus extends Model
{
    use HasFactory;

    /**
     * اسم الجدول المرتبط بالنموذج.
     * (اختياري إذا كان اسم الجدول هو صيغة الجمع بالإنجليزية للنموذج)
     * @var string
     */
    protected $table = 'child_meal_statuses';

    /**
     * المفتاح الأساسي للجدول.
     *
     * @var string
     */
    protected $primaryKey = 'status_id';

    /**
     * السمات التي يمكن إسنادها بشكل جماعي.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'child_id',
        'meal_id',
        'consumption_status',
        'notes',
        'recorded_by_id',
    ];

    /**
     * السمات التي يجب تحويلها إلى أنواع أصلية.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // لا يوجد تحويلات خاصة مطلوبة هنا حاليًا، لكن يمكنك إضافة
        // 'created_at' => 'datetime',
        // 'updated_at' => 'datetime',
        // إذا لم يتم تحويلها تلقائيًا
    ];

    // ------------------------------------------------------------------------
    // العلاقات (Relationships)
    // ------------------------------------------------------------------------

    /**
     * الحصول على الطفل المرتبط بحالة الوجبة هذه.
     * علاقة many-to-one (كل حالة تخص طفل واحد).
     */
    public function child(): BelongsTo
    {
        // يربط هذا النموذج (ChildMealStatus) بنموذج Child
        // باستخدام المفتاح الأجنبي child_id والمفتاح المحلي child_id في جدول children
        return $this->belongsTo(Child::class, 'child_id', 'child_id');
    }

    /**
     * الحصول على الوجبة اليومية المرتبطة بحالة الوجبة هذه.
     * علاقة many-to-one (كل حالة تخص وجبة يومية واحدة).
     */
    public function dailyMeal(): BelongsTo
    {
        // يربط هذا النموذج بنموذج DailyMeal
        // باستخدام المفتاح الأجنبي meal_id والمفتاح المحلي meal_id في جدول daily_meals
        return $this->belongsTo(DailyMeal::class, 'meal_id', 'meal_id');
    }

    /**
     * الحصول على المستخدم الذي قام بتسجيل هذه الحالة (اختياري).
     * علاقة many-to-one (كل حالة قد يتم تسجيلها بواسطة مستخدم واحد).
     */
    public function recordedBy(): BelongsTo
    {
        // يربط هذا النموذج بنموذج User
        // باستخدام المفتاح الأجنبي recorded_by_id والمفتاح المحلي id في جدول users
        return $this->belongsTo(User::class, 'recorded_by_id', 'id');
    }

    // ------------------------------------------------------------------------
    // نطاقات الاستعلام (Query Scopes) - اختياري
    // ------------------------------------------------------------------------

    /**
     * نطاق لجلب الحالات الخاصة بيوم معين.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $date (YYYY-MM-DD)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDate($query, $date)
    {
        // يتطلب ربط مع جدول daily_meals للوصول إلى meal_date
        return $query->whereHas('dailyMeal', function ($mealQuery) use ($date) {
            $mealQuery->whereDate('meal_date', $date);
        });
    }

    /**
     * نطاق لجلب الحالات الخاصة بنوع وجبة معين.
      *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $mealType ('Breakfast', 'Lunch', 'Snack')
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForMealType($query, $mealType)
    {
         // يتطلب ربط مع جدول daily_meals
        return $query->whereHas('dailyMeal', function ($mealQuery) use ($mealType) {
            $mealQuery->where('meal_type', $mealType);
        });
    }

}