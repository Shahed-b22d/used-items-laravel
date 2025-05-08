<?php
namespace App\Http\Controllers;

use App\Models\StoreCategory;
use Illuminate\Http\Request;

class StoreCategoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // هذا هو المتجر المصادق عليه
        $store = auth()->user();

        // تحقق إذا كانت الفئة موجودة مسبقاً في المتجر
        $existingCategory = StoreCategory::where('store_id', $store->id)
                                        ->where('name', $request->name)
                                        ->exists();

        if ($existingCategory) {
            return response()->json([
                'message' => 'This category already exists in your store.'
            ], 400);
        }

        // إضافة الفئة الجديدة
        $category = new StoreCategory();
        $category->name = $request->name;
        $category->store_id = $store->id;
        $category->save();

        return response()->json([
            'message' => 'Category added successfully',
            'category' => $category
        ], 201);
    }
}
