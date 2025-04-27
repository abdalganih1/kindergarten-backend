<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklySchedule extends Model
{
    use HasFactory;

    protected $primaryKey = 'schedule_id';

    protected $fillable = [
        'class_id',
        'day_of_week',
        'start_time',
        'end_time',
        'activity_description',
        'created_by_id',
    ];

    // Define relationships
    public function kindergartenClass()
    {
        return $this->belongsTo(KindergartenClass::class, 'class_id', 'class_id');
    }

    public function createdByAdmin()
    {
        return $this->belongsTo(Admin::class, 'created_by_id', 'admin_id');
    }
}