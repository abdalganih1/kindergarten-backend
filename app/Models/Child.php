<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute; // <-- استيراد Attribute

class Child extends Model
{
    use HasFactory;

    protected $primaryKey = 'child_id';

    protected $fillable = [
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'enrollment_date',
        'class_id',
        'allergies',
        'medical_notes',
        'photo_url',
    ];
    
    protected function fullName(): Attribute // استخدام صيغة Laravel 9+
    {
        return Attribute::make(
            get: fn () => $this->first_name . ' ' . $this->last_name,
        );
    }
    // Define relationships
    public function kindergartenClass() // singular for belongsTo
    {
        return $this->belongsTo(KindergartenClass::class, 'class_id', 'class_id');
    }

    public function parents()
    {
        // Use the correct model name (ParentModel if you renamed it)
        return $this->belongsToMany(ParentModel::class, 'parent_children', 'child_id', 'parent_id');
    }

    public function healthRecords()
    {
        return $this->hasMany(HealthRecord::class, 'child_id', 'child_id');
    }

     public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class, 'child_id', 'child_id');
    }

     public function media()
    {
        return $this->hasMany(Media::class, 'associated_child_id', 'child_id');
    }

     public function observations()
    {
        return $this->hasMany(Observation::class, 'child_id', 'child_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'child_id', 'child_id');
    }
    // In app/Models/Child.php
    public function mealStatuses()
    {
        return $this->hasMany(ChildMealStatus::class, 'child_id', 'child_id');
    }
}