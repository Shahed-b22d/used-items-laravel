<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Earning extends Model
{
    protected $fillable = [
        'seller_type', 'seller_id', 'buyer_id', 'price',
        'commission', 'seller_earning', 'delivery_agent_id',
        'delivery_fee', 'status', 'completed_at',
    ];

    public function seller()
    {
        return $this->morphTo(__FUNCTION__, 'seller_type', 'seller_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function deliveryAgent()
    {
        return $this->belongsTo(DeliveryAgent::class, 'delivery_agent_id');
    }
}

