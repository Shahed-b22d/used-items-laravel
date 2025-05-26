<?php

namespace App\Http\Controllers;

use App\Models\StoreCategory;
use App\Models\Category;
use Illuminate\Http\Request;

class StoreCategoryController extends Controller
{
    // عرض الأقسام الرئيسية والخاصة بالمتجر
    public function index()
    {
        $store = auth()->user();
        
        // جلب الأقسام الخاصة بالمتجر
        $storeCategories = StoreCategory::with('category')
            ->where('store_id', $store->id)
            ->get()
            ->map(function($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'is_main' => !is_null($category->category_id),
                    'main_category' => $category->category ? [
                        'id' => $category->category->id,
                        'name' => $category->category->name
                    ] : null,
                    'created_at' => $category->created_at
                ];
            });

        // جلب الأقسام الرئيسية المتاحة
        $mainCategories = Category::all()->map(function($category) {
            return [
                'id' => $category->id,
                'name' => $category->name
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'store_categories' => $storeCategories,
                'main_categories' => $mainCategories
            ],
            'message' => 'تم جلب الأقسام بنجاح'
        ]);
    }

    // إضافة أقسام للمتجر
    public function store(Request $request)
    {
        $request->validate([
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'new_categories' => 'nullable|array',
            'new_categories.*' => 'string|max:255|distinct',
        ], [
            'category_ids.*.exists' => 'أحد الأقسام المختارة غير موجود',
            'new_categories.*.max' => 'اسم القسم يجب أن لا يتجاوز 255 حرف',
            'new_categories.*.distinct' => 'لا يمكن تكرار نفس اسم القسم'
        ]);

        $store = auth()->user();
        $addedCategories = [];
        $errors = [];

        // إضافة الأقسام الرئيسية المختارة
        if ($request->has('category_ids')) {
            foreach ($request->category_ids as $categoryId) {
                $mainCategory = Category::find($categoryId);
                
                // التحقق من عدم وجود القسم مسبقاً
                $exists = StoreCategory::where('store_id', $store->id)
                    ->where('category_id', $categoryId)
                    ->exists();

                if (!$exists) {
                    $storeCategory = StoreCategory::create([
                        'store_id' => $store->id,
                        'category_id' => $categoryId,
                        'name' => $mainCategory->name
                    ]);
                    $addedCategories[] = [
                        'id' => $storeCategory->id,
                        'name' => $storeCategory->name,
                        'is_main' => true,
                        'main_category' => [
                            'id' => $mainCategory->id,
                            'name' => $mainCategory->name
                        ]
                    ];
                } else {
                    $errors[] = "القسم '{$mainCategory->name}' موجود مسبقاً";
                }
            }
        }

        // إضافة الأقسام الخاصة الجديدة
        if ($request->has('new_categories')) {
            foreach ($request->new_categories as $categoryName) {
                // التحقق من عدم وجود القسم مسبقاً
                $exists = StoreCategory::where('store_id', $store->id)
                    ->where('name', $categoryName)
                    ->exists();

                if (!$exists) {
                    $storeCategory = StoreCategory::create([
                        'store_id' => $store->id,
                        'name' => $categoryName,
                        'category_id' => null
                    ]);
                    $addedCategories[] = [
                        'id' => $storeCategory->id,
                        'name' => $storeCategory->name,
                        'is_main' => false,
                        'main_category' => null
                    ];
                } else {
                    $errors[] = "القسم '{$categoryName}' موجود مسبقاً";
                }
            }
        }

        if (empty($addedCategories) && !empty($errors)) {
            return response()->json([
                'status' => 'error',
                'message' => 'لم يتم إضافة أي أقسام جديدة',
                'errors' => $errors
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => !empty($errors) 
                ? 'تم إضافة بعض الأقسام بنجاح مع وجود بعض الأخطاء'
                : 'تم إضافة جميع الأقسام بنجاح',
            'data' => [
                'added_categories' => $addedCategories,
                'errors' => $errors
            ]
        ], 201);
    }

    // تعديل قسم خاص بالمتجر
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = StoreCategory::findOrFail($id);
        $store = auth()->user();

        if ($category->store_id !== $store->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك بتعديل هذا القسم'
            ], 403);
        }

        // لا يمكن تعديل الأقسام الرئيسية
        if ($category->category_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'لا يمكن تعديل الأقسام الرئيسية'
            ], 400);
        }

        $exists = StoreCategory::where('store_id', $store->id)
            ->where('id', '!=', $id)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'يوجد قسم بهذا الاسم مسبقاً'
            ], 400);
        }

        $category->name = $request->name;
        $category->save();

        return response()->json([
            'status' => 'success',
            'message' => 'تم تعديل القسم بنجاح',
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'is_main' => false,
                'main_category' => null
            ]
        ]);
    }

    // حذف قسم من المتجر
    public function destroy($id)
    {
        $category = StoreCategory::findOrFail($id);
        $store = auth()->user();

        if ($category->store_id !== $store->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك بحذف هذا القسم'
            ], 403);
        }

        // التحقق من وجود منتجات في هذا القسم
        if ($category->products()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'لا يمكن حذف هذا القسم لوجود منتجات مرتبطة به'
            ], 400);
        }

        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف القسم بنجاح'
        ]);
    }
}
