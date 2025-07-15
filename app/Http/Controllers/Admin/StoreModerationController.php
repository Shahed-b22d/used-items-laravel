<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\StoreApproved;
use App\Notifications\StorePaymentReceived;
use Stripe\Webhook;

class StoreModerationController extends Controller
{
    // عرض المتاجر المعلقة
    public function pending()
    {
        $pendingStores = Store::where('is_approved', false)
            ->get()
            ->map(function ($store) {
                return [
                    'id' => $store->id,
                    'name' => $store->name,
                    'email' => $store->email,
                    'commercial_record' => $store->commercial_record,
                    'created_at' => $store->created_at,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'pending_stores' => $pendingStores
            ]
        ]);
    }

public function approve($id)
{
    $store = Store::findOrFail($id);

    if (!$store->has_paid) {
        return response()->json([
            'status' => 'error',
            'message' => 'المتجر لم يقم بالدفع بعد.'
        ], 400);
    }

    if ($store->is_approved) {
        return response()->json([
            'status' => 'error',
            'message' => 'تم الموافقة على هذا المتجر مسبقاً'
        ], 400);
    }

    $store->update(['is_approved' => true]);

    // ✅ إرسال إيميل نصي عادي مباشرة دون قالب
    Mail::raw("مرحباً {$store->name},\n\nتمت الموافقة على حساب متجرك. يمكنك الآن تسجيل الدخول والبدء باستخدام الموقع.\n\nمع تحياتنا، فريق الدعم.", function ($message) use ($store) {
        $message->to($store->email)
                ->subject('تمت الموافقة على حسابك');
    });

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

public function webhook(Request $request)
{
    $payload = $request->all();

    if (($payload['type'] ?? '') === 'payment_intent.succeeded') {
        $paymentIntent = $payload['data']['object'];

        // الحل الأفضل: استخدام store_id من metadata
        $storeId = $paymentIntent['metadata']['store_id'] ?? null;

        if ($storeId) {
            $store = Store::find($storeId);

            if ($store) {
                $store->has_paid = true;
                $store->payment_intent_id = $paymentIntent['id'] ?? null; // خيار إضافي فقط
                $store->save();

                // إرسال إشعار للأدمن
                $admins = Admin::all();
                foreach ($admins as $admin) {
                    $admin->notify(new StorePaymentReceived($store->name));
                }
            }
        }
    }

    return response('Webhook Handled', 200);
}

}
