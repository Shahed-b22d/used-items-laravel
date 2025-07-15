<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Earning;
use App\Models\User;
use App\Models\Store;
use App\Models\DeliveryAgent;
use Carbon\Carbon;

class EarningsReportController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->month;

        $query = Earning::query();

        if ($month) {
            $query->whereMonth('completed_at', '=', $month);
        }

        $earnings = $query->get();

        $storeEarnings = $earnings->where('seller_type', 'store');
        $userEarnings = $earnings->where('seller_type', 'user');
        $deliveryEarnings = $earnings->whereNotNull('delivery_agent_id');

        $storeTotal = $storeEarnings->sum('seller_earning');
        $userTotal = $userEarnings->sum('seller_earning');
        $deliveryTotal = $deliveryEarnings->sum('delivery_fee');
        $platformTotal = $earnings->sum('commission');

        // أسماء البائعين والمندوبين
        $storeDetails = $storeEarnings->map(function ($e) {
            $store = Store::find($e->seller_id);
            return [
                'store_name' => $store?->name,
                'earning' => $e->seller_earning,
            ];
        });

        $userDetails = $userEarnings->map(function ($e) {
            $user = User::find($e->seller_id);
            return [
                'user_name' => $user?->name,
                'earning' => $e->seller_earning,
            ];
        });

        $deliveryDetails = $deliveryEarnings->map(function ($e) {
            $agent = DeliveryAgent::find($e->delivery_agent_id);
            return [
                'agent_name' => $agent?->name,
                'earning' => $e->delivery_fee,
            ];
        });

        return response()->json([
            'store_earnings' => [
                'total' => $storeTotal,
                'details' => $storeDetails,
            ],
            'user_earnings' => [
                'total' => $userTotal,
                'details' => $userDetails,
            ],
            'delivery_agent_earnings' => [
                'total' => $deliveryTotal,
                'details' => $deliveryDetails,
            ],
            'platform_earnings' => $platformTotal,
        ]);
    }
}

