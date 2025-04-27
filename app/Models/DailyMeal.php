<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyMeal extends Model
{
    use HasFactory;

    protected $primaryKey = 'meal_id';

    protected $fillable = [
        'meal_date',
        'meal_type',
        'menu_description',
        'class_id',
    ];

    // Define relationship to Class
    public function kindergartenClass()
    {
        return $this->belongsTo(KindergartenClass::class, 'class_id', 'class_id');
    }
    // In app/Models/DailyMeal.php
    public function childStatuses()
    {
        return $this->hasMany(ChildMealStatus::class, 'meal_id', 'meal_id');
    }
}