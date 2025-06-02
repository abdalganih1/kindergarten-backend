<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $primaryKey = 'event_id';

    protected $fillable = [
        'event_name',
        'description',
        'event_date',
        'location',
        'requires_registration',
        'registration_deadline',
        'created_by_id', // هذا هو user_id للمنشئ
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'event_date' => 'datetime',
        'registration_deadline' => 'datetime',
        'requires_registration' => 'boolean',
    ];

    // ---=== تعديل هذه العلاقة ===---
    /**
     * Get the user who created the event.
     */
    public function creator() // تم تغيير الاسم من createdByAdmin إلى creator
    {
        // يفترض أن created_by_id هو user_id
        return $this->belongsTo(User::class, 'created_by_id', 'id');
    }
    // ---=== نهاية التعديل ===---


    public function registrations()
    {
        return $this->hasMany(EventRegistration::class, 'event_id', 'event_id');
    }

    public function children()
    {
        return $this->belongsToMany(Child::class, 'event_registrations', 'event_id', 'child_id')
                    ->withPivot('registration_date', 'parent_consent')
                    ->withTimestamps();
    }

     public function media()
    {
        return $this->hasMany(Media::class, 'associated_event_id', 'event_id');
    }
}