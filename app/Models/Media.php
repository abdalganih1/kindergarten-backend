<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $primaryKey = 'media_id';

    protected $fillable = [
        'file_path',
        'media_type',
        'description',
        'upload_date', // upload_date is often handled by default timestamp, but we keep it for clarity if defined in migration
        'uploader_id',
        'associated_child_id',
        'associated_event_id',
        'associated_class_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'upload_date' => 'datetime', // <-- أضف هذا السطر
        // created_at and updated_at are usually cast automatically if using timestamps()
        // but you can be explicit:
        // 'created_at' => 'datetime',
        // 'updated_at' => 'datetime',
    ];


    // --- العلاقات ---
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploader_id', 'id');
    }

     public function associatedChild()
    {
        return $this->belongsTo(Child::class, 'associated_child_id', 'child_id');
    }

    public function associatedEvent()
    {
        return $this->belongsTo(Event::class, 'associated_event_id', 'event_id');
    }

    public function associatedClass()
    {
        return $this->belongsTo(KindergartenClass::class, 'associated_class_id', 'class_id');
    }
}