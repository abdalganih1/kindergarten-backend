<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // If using custom primary key name uncomment below
    // protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Add role
        'is_active', // Add is_active
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Ensures password is automatically hashed
        'is_active' => 'boolean', // Cast to boolean
    ];

    // Define relationships here later (e.g., to Admin, Parent profiles)
    public function adminProfile()
    {
        return $this->hasOne(Admin::class, 'user_id', 'id'); // Assuming user PK is 'id'
    }

    public function parentProfile()
    {
        return $this->hasOne(ParentModel::class, 'user_id', 'id'); // Assuming user PK is 'id'
    }
    /**
 * The classes that the supervisor is assigned to.
 * (Only relevant if user role is Supervisor)
 */
public function supervisedClasses(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
{
    // تأكد من استخدام اسم الجدول الوسيط الصحيح وأسماء الأعمدة
    return $this->belongsToMany(KindergartenClass::class, 'supervisor_classes', 'user_id', 'class_id');
}

 // --- إضافة ملف تعريف للمشرف (اختياري ولكن جيد للتنظيم) ---
 /**
  * Get the supervisor profile associated with the user.
  */
 public function supervisorProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
 {
     return $this->hasOne(Admin::class, 'user_id', 'id'); // افترض وجود نموذج Supervisor
 }
}