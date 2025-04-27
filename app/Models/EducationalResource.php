<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationalResource extends Model
{
    use HasFactory;

    protected $primaryKey = 'resource_id';

    protected $fillable = [
        'title',
        'description',
        'resource_type',
        'url_or_path',
        'target_age_min',
        'target_age_max',
        'subject',
        'added_by_id',
    ];

    // Define relationship to Admin who added it
    public function addedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'added_by_id', 'admin_id');
    }
}