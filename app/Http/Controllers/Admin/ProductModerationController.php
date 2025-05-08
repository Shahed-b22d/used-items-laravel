<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductModerationController extends Controller
{
    public function pending()
    {
        $products = Product::where('is_approved', 0)->get();
        return response()->json($products);
    }

    // موافقة الأدمن على المنتج
    public function approve($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->is_approved = 1;
        $product->save();

        return response()->json(['message' => 'Product approved successfully']);
    }

    // رفض المنتج (حذفه من قاعدة البيانات)
    public function reject($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Product rejected and deleted']);
    }

    // عرض جميع المنتجات مع حالة الموافقة
public function allProducts()
{
    $products = \App\Models\Product::select('id', 'name', 'description', 'price', 'is_approved')->get();

    return response()->json($products);
}

}
