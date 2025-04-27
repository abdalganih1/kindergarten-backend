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
        'created_by_id',
    ];

    // Define relationships
    public function createdByAdmin()
    {
        return $this->belongsTo(Admin::class, 'created_by_id', 'admin_id');
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class, 'event_id', 'event_id');
    }

    public function children() // Children registered for this event
    {
        return $this->belongsToMany(Child::class, 'event_registrations', 'event_id', 'child_id')
                    ->withPivot('registration_date', 'parent_consent') // Include extra pivot columns if needed
                    ->withTimestamps(); // If event_registrations has timestamps
    }

     public function media()
    {
        return $this->hasMany(Media::class, 'associated_event_id', 'event_id');
    }
}