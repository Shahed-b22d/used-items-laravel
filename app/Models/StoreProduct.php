<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreProduct extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'store_category_id', 'store_id'];

    // علاقة مع المتجر
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // علاقة مع القسم
    public function category()
    {
        return $this->belongsTo(StoreCategory::class, 'store_category_id');
    }
}
