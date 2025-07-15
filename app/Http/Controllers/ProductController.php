<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // عرض كل المنتجات المعتمدة وغير المباعة فقط
    public function index()
{
    $products = Product::where('is_approved', true)
        ->with(['category', 'user'])
        ->latest()
        ->paginate(10);

    $products->getCollection()->transform(function ($product) {
        $product->status = $product->is_sold || $product->quantity <= 0 ? 'مباع' : 'متاح';
        return $product;
    });

    return response()->json([
        'status' => 'success',
        'data' => $products,
        'message' => 'تم جلب المنتجات بنجاح'
    ]);
}

    // رفع منتج جديد (is_sold تلقائيًا = false، لا داعي لذكره)
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
            'user_id' => Auth::id(),
            'location' => $request->location,
            'quantity' => $request->quantity,
            'image' => $imagePath,
            'price' => $request->price,
            'is_approved' => false, // بانتظار موافقة الأدمن
            // 'is_sold' => false ← ماله داعي لأن default في الداتابيز
        ]);

        return response()->json([
            'message' => 'تم إرسال المنتج بنجاح بانتظار موافقة الأدمن.',
            'product' => $product
        ], 201);
    }

    // عرض جميع المنتجات الخاصة بالمستخدم الحالي (مع حالتها)
    public function myProducts()
    {
        $user = Auth::user();

        $products = Product::where('user_id', $user->id)
            ->with(['category'])
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $products,
            'message' => 'تم جلب منتجاتك بنجاح'
        ]);
    }

    // ✅ يمكنك لاحقًا إضافة دالة للأدمن لتغيير حالة منتج إلى "مباع"
    // public function markAsSold($id) { ... }
}
