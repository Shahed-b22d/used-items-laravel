<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoreModerationController extends Controller
{
    // عرض المتاجر المعلقة
    public function pending()
    {
        $pendingStores = Store::where('is_approved', false)
            ->get()
            ->map(function($store) {
                return [
                    'id' => $store->id,
                    'name' => $store->name,
                    'email' => $store->email,
                    'commercial_record' => $store->commercial_record,
                    'created_at' => $store->created_at
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'pending_stores' => $pendingStores
            ]
        ]);
    }

    // الموافقة على متجر
    public function approve($id)
    {
        $store = Store::findOrFail($id);

        if ($store->is_approved) {
            return response()->json([
                'status' => 'error',
                'message' => 'تم الموافقة على هذا المتجر مسبقاً'
            ], 400);
        }

        $store->update(['is_approved' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'تمت الموافقة على المتجر',
            'data' => [
                'name' => $store->name,
                'email' => $store->email
            ]
        ]);
    }

    // رفض متجر (حذفه)
    public function reject($id)
    {
        $store = Store::findOrFail($id);

        // حذف الملف المرفق من التخزين
        if ($store->commercial_record) {
            Storage::disk('public')->delete($store->commercial_record);
        }

        // حذف المتجر
        $store->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم رفض المتجر وحذفه بنجاح'
        ]);
    }
}
