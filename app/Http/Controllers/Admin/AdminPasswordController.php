<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\Admin;
use App\Notifications\AdminResetPasswordNotification;
use Illuminate\Support\Facades\Auth;

class AdminPasswordController extends Controller
{
    /**
     * ✅ تغيير كلمة المرور للأدمن وهو مسجل دخول
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $admin = Auth::user(); // أو auth('sanctum')->user()

        if (!$admin || !($admin instanceof Admin)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!Hash::check($request->old_password, $admin->password)) {
            return response()->json(['message' => 'Old password is incorrect'], 400);
        }

        $admin->password = Hash::make($request->new_password);
        $admin->save();

        return response()->json(['message' => 'Password changed successfully']);
    }

    /**
     * ✅ إرسال رمز إعادة تعيين كلمة المرور إلى البريد (عند النسيان)
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:admins,email'
        ]);

        $admin = Admin::where('email', $request->email)->first();

        // حذف الرموز السابقة
        DB::table('password_reset_tokens')->where('email', $admin->email)->delete();

        $token = Str::random(64);

        // تخزين الرمز في قاعدة البيانات
        DB::table('password_reset_tokens')->insert([
            'email' => $admin->email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now()
        ]);

        // إرسال إشعار بالبريد (Mailtrap)
        $admin->notify(new AdminResetPasswordNotification($token));

        return response()->json([
            'status' => 'success',
            'message' => 'تم إرسال رمز إعادة تعيين كلمة المرور إلى بريدك الإلكتروني'
        ]);
    }

    /**
     * ✅ تعيين كلمة مرور جديدة باستخدام الرمز
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:admins,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset|| !Hash::check($request->token, $passwordReset->token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'رمز غير صالح'
            ], 400);
        }

        // التحقق من مدة صلاحية الرمز
        if (Carbon::parse($passwordReset->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return response()->json([
                'status' => 'error',
                'message' => 'رمز إعادة التعيين منتهي الصلاحية'
            ], 400);
        }

        $admin = Admin::where('email', $request->email)->first();
        $admin->password = Hash::make($request->password);
        $admin->save();

        // حذف الرمز بعد الاستخدام
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم تعيين كلمة المرور الجديدة بنجاح'
        ]);
    }
}
