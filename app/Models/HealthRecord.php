<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthRecord extends Model
{
    use HasFactory;

    protected $primaryKey = 'record_id';

    protected $fillable = [
        'child_id',
        'record_type',
        'record_date',
        'details',
        'next_due_date',
        'document_path',
        'entered_by_id',
    ];
    
    // Define relationships
    public function child()
    {
        return $this->belongsTo(Child::class, 'child_id', 'child_id');
    }

    public function enteredByUser()
    {
        return $this->belongsTo(User::class, 'entered_by_id', 'id');
    }
}