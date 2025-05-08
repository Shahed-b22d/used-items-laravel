<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\StoreAuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Admin\UserStoreController;
use App\Http\Controllers\Admin\ProductModerationController;
use App\Http\Controllers\Admin\StoreModerationController;
use App\Http\Controllers\StoreProductController;
use App\Http\Controllers\StoreCategoryController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('user')->group(function () {
    Route::post('/register', [UserAuthController::class, 'register']);
    Route::post('/login', [UserAuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [UserAuthController::class, 'logout']);
});


Route::prefix('store')->group(function () {
    Route::post('/register', [StoreAuthController::class, 'register']);
    Route::post('/login', [StoreAuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [StoreAuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('categories', CategoryController::class);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
});



Route::post('/admin/login', [AdminAuthController::class, 'login']);

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    // عرض كل المستخدمين والمتاجر
    Route::get('/users', [UserStoreController::class, 'getUsers']);
    Route::get('/stores', [UserStoreController::class, 'getStores']);

    // حذف مستخدم أو متجر
    Route::delete('/users/{id}', [UserStoreController::class, 'deleteUser']);
    Route::delete('/stores/{id}', [UserStoreController::class, 'deleteStore']);
});


Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/products/pending', [ProductModerationController::class, 'pending']);
    Route::post('/products/{id}/approve', [ProductModerationController::class, 'approve']);
    Route::delete('/products/{id}/reject', [ProductModerationController::class, 'reject']);
});

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/stores/pending', [StoreModerationController::class, 'pending']);
    Route::post('/stores/{id}/approve', [StoreModerationController::class, 'approve']);
    Route::delete('/stores/{id}/reject', [StoreModerationController::class, 'reject']);
});

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/products/all', [ProductModerationController::class, 'allProducts']);
});


Route::middleware('auth:sanctum')->group(function () {
    // مسار لإضافة الأقسام وعرضها
    Route::post('/store/categories', [StoreCategoryController::class, 'store']);
    Route::get('/store/categories', [StoreCategoryController::class, 'index']);

    // مسار لإضافة منتج إلى قسم معين
    Route::post('/store/categories/{categoryId}/products', [StoreProductController::class, 'store']);
});
