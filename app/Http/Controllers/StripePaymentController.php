<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Product;
use App\Models\StoreProduct;
use App\Services\EarningService;
use Stripe\Checkout\Session as StripeSession;

class StripePaymentController extends Controller
{
    public function __construct()
    {
        // اجعلي success تمر من auth:sanctum فقط لو حابة تعتمدي على التوكن
        // حالياً نتركها اختيارية
    }

    public function checkout(Request $request)
{
    $request->validate([
        'product_id' => 'required',
        'product_type' => 'required|in:store,user',
        'buyer_id' => 'required|exists:users,id', // ← تمرير الـ buyer_id من الواجهة أو Postman
    ]);

    if ($request->product_type === 'store') {
        $product = StoreProduct::findOrFail($request->product_id);
    } else {
        $product = Product::findOrFail($request->product_id);
    }

    if ($product->quantity < 1 || ($product->is_sold ?? false)) {
        return response()->json(['message' => 'هذا المنتج تم بيعه بالفعل.'], 400);
    }

    \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

    $session = \Stripe\Checkout\Session::create([
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => $product->name,
                ],
                'unit_amount' => $product->price * 100,
            ],
            'quantity' => 1,
        ]],
        'metadata' => [
            'buyer_id' => $request->buyer_id, // ← نحفظه بشكل آمن في metadata
            'product_id' => $product->id,
            'product_type' => $request->product_type,
        ],
        'mode' => 'payment',
        'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => route('payment.cancel'),
    ]);

    return response()->json(['url' => $session->url]);
}


public function success(Request $request, \App\Services\EarningService $earningService)
{
    \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

    $sessionId = $request->get('session_id');
    if (!$sessionId) {
        return response()->json(['message' => 'Session ID مفقود'], 400);
    }

    $session = \Stripe\Checkout\Session::retrieve($sessionId);

    // استرجاع البيانات من metadata
    $buyerId = $session->metadata->buyer_id;
    $productId = $session->metadata->product_id;
    $productType = $session->metadata->product_type;

    if (!$buyerId || !$productId || !$productType) {
        return response()->json(['message' => 'بيانات الدفع غير مكتملة.'], 400);
    }

    if ($productType === 'store') {
        $product = StoreProduct::findOrFail($productId);
        $sellerId = $product->store_id;
    } else {
        $product = Product::findOrFail($productId);
        $sellerId = $product->user_id;
    }

    if ($product->quantity > 1) {
        $product->quantity -= 1;
    } else {
        $product->is_sold = true;
    }

    $product->save();

    $earningService->createEarning(
        $productType,
        $sellerId,
        $buyerId,
        $product->price
    );

    return response()->json(['message' => 'تم الدفع وتسجيل الأرباح بنجاح.']);
}

    public function cancel()
    {
        return response()->json(['message' => 'تم إلغاء عملية الدفع.']);
    }
}
