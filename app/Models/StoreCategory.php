<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreCategory extends Model
{
    use HasFactory;

    // أضف category_id هنا لأنه صار ممكن تحطه في قاعدة البيانات
    protected $fillable = ['name', 'store_id', 'category_id'];

    // علاقة مع المتجر
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // علاقة مع القسم العام (Category)
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
