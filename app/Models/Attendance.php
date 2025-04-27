<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $primaryKey = 'attendance_id';

    protected $fillable = [
        'child_id',
        'attendance_date',
        'status',
        'check_in_time',
        'check_out_time',
        'notes',
        'recorded_by_id',
    ];

    // Define relationships
    public function child()
    {
        return $this->belongsTo(Child::class, 'child_id', 'child_id');
    }

    public function recordedByUser()
    {
        return $this->belongsTo(User::class, 'recorded_by_id', 'id');
    }
}