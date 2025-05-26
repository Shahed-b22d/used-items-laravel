<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;

class CartController extends Controller
{
     // 1. إضافة منتج للسلة
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($request->product_id);

        if ($product->quantity < $request->quantity) {
            return response()->json(['message' => 'الكمية المطلوبة غير متوفرة'], 400);
        }

        $cartItem = Cart::where('user_id', auth()->id())
                        ->where('product_id', $product->id)
                        ->first();

        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            Cart::create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
                'quantity' => $request->quantity,
            ]);
        }

        return response()->json(['message' => 'تمت إضافة المنتج إلى السلة']);
    }

    // 2. عرض السلة
    public function viewCart()
    {
        $user = auth()->user();

        // حذف المنتجات الغير متوفرة
        Cart::where('user_id', $user->id)
            ->whereHas('product', function ($query) {
                $query->where('quantity', '<=', 0);
            })->delete();

        $cartItems = Cart::with('product')
                        ->where('user_id', $user->id)
                        ->get();

        return response()->json($cartItems);
    }

    // 3. تحديث الكمية
    public function updateQuantity(Request $request, $product_id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = Cart::where('user_id', auth()->id())
                        ->where('product_id', $product_id)
                        ->first();

        if (!$cartItem) {
            return response()->json(['message' => 'المنتج غير موجود في السلة'], 404);
        }

        $product = $cartItem->product;

        if ($product->quantity < $request->quantity) {
            return response()->json(['message' => 'الكمية المطلوبة غير متوفرة'], 400);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json(['message' => 'تم تحديث الكمية بنجاح']);
    }

    // 4. حذف منتج من السلة
    public function removeFromCart($product_id)
    {
        $cartItem = Cart::where('user_id', auth()->id())
                        ->where('product_id', $product_id)
                        ->first();

        if (!$cartItem) {
            return response()->json([
                'message' => 'المنتج غير موجود في السلة'
            ], 404);
        }

        $cartItem->delete();

        return response()->json([
            'message' => 'تم حذف المنتج من السلة'
        ]);
    }

}
