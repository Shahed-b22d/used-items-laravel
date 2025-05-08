<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreModerationController extends Controller
{
     // عرض المتاجر المعلقة
    public function pending()
    {
        $stores = Store::where('is_approved', 0)->get();
        return response()->json($stores);
    }

    // الموافقة على متجر
    public function approve($id)
    {
        $store = Store::find($id);

        if (!$store) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        $store->is_approved = 1;
        $store->save();

        return response()->json(['message' => 'Store approved successfully']);
    }

    // رفض متجر (حذفه)
    public function reject($id)
    {
        $store = Store::find($id);

        if (!$store) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        $store->delete();

        return response()->json(['message' => 'Store rejected and deleted']);
    }
}
