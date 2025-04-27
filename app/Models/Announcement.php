<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $primaryKey = 'announcement_id';

    protected $fillable = [
        'title',
        'content',
        'publish_date',
        'author_id',
        'target_class_id',
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'publish_date' => 'datetime', // <-- أضف هذا السطر
        // أضف أي حقول أخرى تحتاج لتحويل نوعها هنا
        // 'created_at' => 'datetime', // يتم تحويلها تلقائيًا غالبًا
        // 'updated_at' => 'datetime', // يتم تحويلها تلقائيًا غالبًا
    ];
    
    // Define relationships
    public function author() // Admin who wrote it
    {
        return $this->belongsTo(Admin::class, 'author_id', 'admin_id');
    }

    public function targetClass()
    {
        return $this->belongsTo(KindergartenClass::class, 'target_class_id', 'class_id');
    }
}