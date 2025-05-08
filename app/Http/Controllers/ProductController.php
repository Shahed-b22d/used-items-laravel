<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'category_id' => 'required|exists:categories,id',
        'location' => 'required|string|max:255',
        'quantity' => 'required|integer|min:1',
        'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'price' => 'required|numeric|min:0',
    ]);

    $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('products', 'public');
    }

    $product = Product::create([
        'name' => $request->name,
        'description' => $request->description,
        'category_id' => $request->category_id,
        'user_id' => auth()->id(),
        'location' => $request->location,
        'quantity' => $request->quantity,
        'image' => $imagePath,
        'price' => $request->price,
        'is_approved' => false, // بانتظار موافقة الأدمن

    ]);

    return response()->json([
        'message' => 'تم إرسال المنتج بنجاح بانتظار موافقة الأدمن.',
        'product' => $product
    ], 201);
}
}
