<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    use HasFactory;

    protected $primaryKey = 'registration_id';

    protected $fillable = [
        'event_id',
        'child_id',
        'registration_date',
        'parent_consent',
    ];

    protected $casts = [
        'registration_date' => 'datetime', // <-- أضف هذا السطر
        'parent_consent' => 'boolean',      // <-- جيد أيضًا تحويل هذا إلى boolean
    ];

    // Define relationships
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function child()
    {
        return $this->belongsTo(Child::class, 'child_id', 'child_id');
    }
}