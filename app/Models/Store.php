<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Store extends Model
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'commercial_record', // هذا لحفظ صورة أو ملف السجل التجاري
        'is_approved',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

}
