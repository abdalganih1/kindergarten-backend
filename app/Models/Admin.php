<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $table = 'admins'; // Explicitly define table name if needed
    protected $primaryKey = 'admin_id'; // Define custom primary key

    protected $fillable = [
        'user_id',
        'full_name',
        'contact_email',
        'contact_phone',
    ];

    // Define relationship back to User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id'); // Assuming user PK is 'id'
    }

    // Define other relationships (e.g., announcements created, events created)
}