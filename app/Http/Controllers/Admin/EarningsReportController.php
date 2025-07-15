<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Earning;
use App\Models\User;
use App\Models\Store;
use App\Models\DeliveryAgent;

class EarningsReportController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->query('month');
        $year = $request->query('year');

        $query = Earning::query();

        if ($month && $year) {
            $query->whereMonth('completed_at', $month)
                ->whereYear('completed_at', $year);
        } elseif ($month) {
            $query->whereMonth('completed_at', $month);
        } elseif ($year) {
            $query->whereYear('completed_at', $year);
        }

        $earnings = $query->get();

        // جمع الأرباح لكل متجر
        $storeEarnings = $earnings->where('seller_type', 'store')
            ->groupBy('seller_id')
            ->map(function ($items, $storeId) {
                $storeName = Store::find($storeId)?->name ?? 'غير معروف';
                return [
                    'store_id' => $storeId,
                    'store_name' => $storeName,
                    'total_earning' => $items->sum('seller_earning'),
                    'total_commission' => $items->sum('commission'),
                ];
            })->values();

        // جمع أرباح المستخدمين
        $userEarnings = $earnings->where('seller_type', 'user')
            ->groupBy('seller_id')
            ->map(function ($items, $userId) {
                $userName = User::find($userId)?->name ?? 'غير معروف';
                return [
                    'user_id' => $userId,
                    'user_name' => $userName,
                    'total_earning' => $items->sum('seller_earning'),
                    'total_commission' => $items->sum('commission'),
                ];
            })->values();

        // جمع أرباح المندوبين (توصيل)
        $deliveryEarnings = $earnings->whereNotNull('delivery_agent_id')
            ->groupBy('delivery_agent_id')
            ->map(function ($items, $agentId) {
                $agentName = DeliveryAgent::find($agentId)?->name ?? 'غير معروف';
                return [
                    'agent_id' => $agentId,
                    'agent_name' => $agentName,
                    'total_delivery_fee' => $items->sum('delivery_fee'),
                ];
            })->values();

        // إجمالي أرباح المنصة (كل النسب المقتطعة)
        $platformTotal = $earnings->sum('commission');

        return response()->json([
            'stores' => $storeEarnings,
            'users' => $userEarnings,
            'delivery_agents' => $deliveryEarnings,
            'platform_total_commission' => $platformTotal,
        ]);
    }
}

