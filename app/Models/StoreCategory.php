<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'store_id'];

    // علاقة مع المتجر
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
