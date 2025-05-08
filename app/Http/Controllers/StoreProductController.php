<?php
namespace App\Http\Controllers;

use App\Models\StoreProduct;
use App\Models\StoreCategory;
use Illuminate\Http\Request;

class StoreProductController extends Controller
{
    public function store(Request $request, $categoryId)
    {
    $category = StoreCategory::find($categoryId);

    // التحقق من وجود الفئة
    if (!$category) {
        return response()->json(['message' => 'Category not found.'], 404);
    }

    // التحقق من صحة المدخلات من المستخدم
    $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric',
        'stock' => 'required|integer',
        'description' => 'nullable|string',
    ]);

    // الحصول على المتجر من التوكن
    $store = auth()->user();

    // تحقق أن هذا القسم تابع لهذا المتجر
    if ($category->store_id !== $store->id) {
        return response()->json([
            'message' => 'You do not have permission to add products to this category.'
        ], 403);
    }

    // التحقق من وجود المنتج في نفس الفئة
    $existingProduct = StoreProduct::where('name', $request->name)
                    ->where('category_id', $category->id)
                    ->exists();

    if ($existingProduct) {
        return response()->json([
            'message' => 'This product already exists in the selected category.'
        ], 400);
    }

    // إضافة المنتج الجديد
    $product = new StoreProduct();
    $product->store_id = $store->id;
    $product->name = $request->name;
    $product->price = $request->price;
    $product->category_id = $category->id;
    $product->stock = $request->stock;
    $product->description = $request->description;
    $product->save();

    return response()->json([
        'message' => 'Product added successfully',
        'product' => $product
    ], 201);
}
}
