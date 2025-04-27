<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Rename class if you renamed the file
class ParentModel extends Model
{
    use HasFactory;

    protected $table = 'parents';
    protected $primaryKey = 'parent_id';

    protected $fillable = [
        'user_id',
        'full_name',
        'contact_email',
        'contact_phone',
        'address',
    ];

     // Define relationship back to User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id'); // Assuming user PK is 'id'
    }

    // Define relationship to Children (Many-to-Many)
    public function children()
    {
        return $this->belongsToMany(Child::class, 'parent_children', 'parent_id', 'child_id');
    }

    // Define relationship to Observations
    public function observations()
    {
        return $this->hasMany(Observation::class, 'parent_id', 'parent_id');
    }
}