<?php

namespace App\Services;

use App\Models\Earning;

class EarningService
{
    public function createEarning($sellerType, $sellerId, $buyerId, $price, $deliveryAgentId = null, $deliveryFee = 0)
    {
        $commission = $sellerType === 'store' ? $price * 0.05 : $price * 0.10;
        $sellerEarning = $price - $commission;

        return Earning::create([
            'seller_type'       => $sellerType,
            'seller_id'         => $sellerId,
            'buyer_id'          => $buyerId,
            'price'             => $price,
            'commission'        => $commission,
            'seller_earning'    => $sellerEarning,
            'delivery_agent_id' => $deliveryAgentId,
            'delivery_fee'      => $deliveryFee,
            'status'            => 'completed',
            'completed_at'      => now(),
        ]);
    }
}

