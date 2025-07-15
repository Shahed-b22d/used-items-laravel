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
use App\Http\Controllers\Admin\CategoryAdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\Admin\DeliveryAgentController;
use App\Http\Controllers\DeliveryAgentAuthController;
use App\Http\Controllers\Admin\AdminPasswordController;
use App\Http\Controllers\UserPasswordController;
use App\Http\Controllers\StorePasswordController;
use App\Http\Controllers\DeliveryAgentPasswordController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\Admin\EarningsReportController;



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

//Route::middleware('auth:sanctum')->group(function () {
    //Route::apiResource('categories', CategoryController::class);
  //});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
});



Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout']);
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
    Route::get('/products/pending', [ProductModerationController::class, 'pending']);  //المنتجات التي لم يتم الموافقة عليها بعد
    Route::post('/products/{id}/approve', [ProductModerationController::class, 'approve']);  //الموافقة على منتج
    Route::delete('/products/{id}/reject', [ProductModerationController::class, 'reject']);  // رفض منتج
});

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/stores/pending', [StoreModerationController::class, 'pending']);  //المخازن التي لم يتم الموافقة عليها بعد
    Route::post('/stores/{id}/approve', [StoreModerationController::class, 'approve']); //الموافقة على مخزن
    Route::delete('/stores/{id}/reject', [StoreModerationController::class, 'reject']);  // رفض مخزن
});

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/products/all', [ProductModerationController::class, 'allProducts']);
});


//Route::middleware('auth:sanctum')->group(function () {
    // مسار لإضافة الأقسام وعرضها
    //Route::post('/store/categories', [StoreCategoryController::class, 'store']);
    //Route::get('/store/categories', [StoreCategoryController::class, 'index']);

    // مسار لإضافة منتج إلى قسم معين
    //Route::post('/store/categories/{categoryId}/products', [StoreProductController::class, 'store']);
//});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index']);
});


Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/categories', [CategoryAdminController::class, 'index']);
    Route::post('/categories', [CategoryAdminController::class, 'store']);
    Route::put('/categories/{id}', [CategoryAdminController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryAdminController::class, 'destroy']);
});



Route::middleware('auth:sanctum')->group(function () {
    Route::post('/cart', [CartController::class, 'addToCart']);           // إضافة منتج
    Route::get('/cart', [CartController::class, 'viewCart']);             // عرض السلة
    Route::put('/cart/{product_id}', [CartController::class, 'updateQuantity']); // تحديث كمية
    Route::delete('/cart/{product_id}', [CartController::class, 'removeFromCart']); // حذف منتج
});


// مسار عرض جميع المنتجات المعتمدة والغير مباعة
Route::get('/products', [ProductController::class, 'index']);

// مسار لعرض جميع المنتجات التي قام المستخدم برفعها حتى لو كانت غير معتمدة أو حتى لو كانت مباعة.
Route::middleware('auth:sanctum')->get('/my-products', [ProductController::class, 'myProducts']);


// كلمة المرور
Route::middleware('auth:sanctum')->post('/admin/change-password', [AdminPasswordController::class, 'changePassword']);

// نسيان كلمة المرور
Route::post('/admin/forgot-password', [AdminPasswordController::class, 'forgotPassword']);
Route::post('/admin/reset-password', [AdminPasswordController::class, 'resetPassword']);


Route::middleware(['auth:sanctum', 'identify.actor'])->group(function () {
    Route::post('/complaints', [ComplaintController::class, 'store']);
    Route::get('/complaints', [ComplaintController::class, 'index']); // فقط admin
});

Route::middleware(['auth:sanctum', 'auth.store'])->group(function () {
    // إضافة وعرض وتعديل وحذف فئات المتجر
    Route::post('/store/categories', [StoreCategoryController::class, 'store']);
    Route::get('/store/categories', [StoreCategoryController::class, 'index']);
    Route::put('/store/categories/{id}', [StoreCategoryController::class, 'update']);
    Route::delete('/store/categories/{id}', [StoreCategoryController::class, 'destroy']);

    // إضافة وعرض وتعديل وحذف منتجات المتجر
    Route::post('/store/categories/{categoryId}/products', [StoreProductController::class, 'store']);
    Route::get('/store/products', [StoreProductController::class, 'index']);
    Route::put('/store/products/{id}', [StoreProductController::class, 'update']);
    Route::delete('/store/products/{id}', [StoreProductController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/delivery-agents', [DeliveryAgentController::class, 'index']);
    Route::post('/delivery-agents', [DeliveryAgentController::class, 'store']);
    Route::put('/delivery-agents/{id}', [DeliveryAgentController::class, 'update']);
    Route::delete('/delivery-agents/{id}', [DeliveryAgentController::class, 'destroy']);
});


Route::prefix('delivery-agents')->group(function () {
    Route::post('login', [DeliveryAgentAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', function (Request $request) {
            return $request->user();
        });

        Route::post('logout', [DeliveryAgentAuthController::class, 'logout']);
        Route::put('update-profile', [DeliveryAgentAuthController::class, 'updateProfile']);
        // APIs أخرى خاصة بالمندوب يمكن إضافتها هنا
    });
});


Route::middleware('auth:sanctum')->post('/user/change-password', [UserPasswordController::class, 'change']);
Route::post('/forgot-password', [UserPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [UserPasswordController::class, 'reset']);


Route::prefix('store')->group(function () {
    Route::post('/change-password', [StorePasswordController::class, 'changePassword'])->middleware('auth:sanctum');
    Route::post('/forgot-password', [StorePasswordController::class, 'forgotPassword']);
    Route::post('/reset-password', [StorePasswordController::class, 'resetPassword']);
});


Route::prefix('delivery-agent')->group(function () {
    Route::post('/change-password', [DeliveryAgentPasswordController::class, 'changePassword'])->middleware('auth:sanctum');
    Route::post('/forgot-password', [DeliveryAgentPasswordController::class, 'forgotPassword']);
    Route::post('/reset-password', [DeliveryAgentPasswordController::class, 'resetPassword']);
});


// ✅ إنشاء جلسة الدفع عبر Stripe
Route::post('/checkout', [StripePaymentController::class, 'checkout']);

// ✅ عند نجاح الدفع (لا تحميه بأي middleware!)
Route::get('/payment-success', [StripePaymentController::class, 'success'])->name('payment.success');

// ✅ عند إلغاء الدفع
Route::get('/payment-cancel', [StripePaymentController::class, 'cancel'])->name('payment.cancel');

Route::get('/payment-success', [StripePaymentController::class, 'success'])->name('payment.success');

// Webhook استدعاء من Stripe لمعالجة الدفع
Route::post('/webhook/stripe', [StoreModerationController::class, 'webhook']);

// API للأدمن
Route::get('/admin/earnings', [EarningsReportController::class, 'index']);
