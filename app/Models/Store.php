<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Store extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // الحقول التي يمكن ملؤها بواسطة المستخدم
    protected $fillable = [
        'name',
        'email',
        'password',
        'commercial_record',
        'is_approved',
    ];

    // الحقول المخفية (مثل كلمة المرور)
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // العلاقة مع الأقسام (التي تخص المتجر)
    public function categories()
    {
        return $this->hasMany(StoreCategory::class);
    }

    // العلاقة مع المنتجات (التي تخص المتجر)
    public function products()
    {
        return $this->hasMany(StoreProduct::class);
    }

    // العلاقة مع المستخدم الذي يملك هذا المتجر
    public function user()
    {
        return $this->belongsTo(User::class); // افترض أن المتجر ينتمي إلى مستخدم
    }

    // وظيفة لإضافة التوكن الخاص بالمتجر عند التسجيل
    public function createApiToken()
    {
        return $this->createToken('StoreApp')->plainTextToken;
    }

public function complaints()
{
    return $this->morphMany(Complaint::class, 'complainable');
}
}
