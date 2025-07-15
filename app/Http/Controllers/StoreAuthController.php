<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;  // استيراد Mail
use App\Models\Store;

class StoreAuthController extends Controller
{
    public function register(Request $request)
    {
        // التحقق من صحة البيانات الواردة
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:stores',
            'password' => 'required|string|min:8',
            'commercial_record' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات غير صالحة',
                'errors' => $validator->errors()
            ], 422);
        }

        // تخزين الملف
        $imagePath = $request->file('commercial_record')->store('commercial_records', 'public');

        // إنشاء سجل المتجر الجديد مع الإعدادات الأساسية
        $store = Store::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'commercial_record' => $imagePath,
            'is_approved' => false,
            'has_paid' => false,
        ]);

        // إرسال إيميل نصي مباشر بدون مابيلابل ولا قوالب
        $amount = '100$';  // مبلغ الدفع (يمكن تغييره حسب الحاجة)
        $accountNumber = '123456789';  // رقم الحساب (يمكن تغييره حسب الحاجة)

        $messageBody = "مرحباً {$store->name}\n\n";
        $messageBody .= "يرجى دفع المبلغ {$amount} إلى رقم الحساب التالي:\n";
        $messageBody .= "رقم الحساب: {$accountNumber}\n\n";
        $messageBody .= "شكراً لتسجيلك في موقعنا.";

        Mail::raw($messageBody, function ($message) use ($store) {
            $message->subject('تعليمات الدفع');
            $message->to($store->email);
        });

        // الرد بنجاح
        return response()->json([
            'status' => 'success',
            'message' => 'تم استلام طلب التسجيل وسيتم مراجعته من قبل الإدارة. يرجى التحقق من بريدك لإرشادات الدفع.',
            'data' => [
                'name' => $store->name,
                'email' => $store->email
            ]
        ], 201);
    }

 public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|string'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'بيانات غير صالحة',
            'errors' => $validator->errors()
        ], 422);
    }

    $store = Store::where('email', $request->email)->first();

    if (!$store || !Hash::check($request->password, $store->password)) {
        return response()->json([
            'status' => 'error',
            'message' => 'بيانات الدخول غير صالحة'
        ], 401);
    }

    // التحقق من حالة الموافقة
    if (!$store->is_approved) {
        return response()->json([
            'status' => 'error',
            'message' => 'حسابك قيد المراجعة من قبل الإدارة'
        ], 403);
    }

    $token = $store->createToken('store_token')->plainTextToken;

    return response()->json([
        'status' => 'success',
        'message' => 'تم تسجيل الدخول بنجاح',
        'data' => [
            'token' => $token,
            'store' => [
                'name' => $store->name,
                'email' => $store->email
            ]
        ]
    ]);
}

public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();
    return response()->json([
        'status' => 'success',
        'message' => 'تم تسجيل الخروج بنجاح'
    ]);
}

}
