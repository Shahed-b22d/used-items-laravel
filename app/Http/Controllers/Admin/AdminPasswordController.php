<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

class AdminPasswordController extends Controller
{
    public function update(Request $request)
    {
    $request->validate([
        'email' => 'required|email|exists:admins,email',
    ]);

    $admin = Admin::where('email', $request->email)->first();
    $token = app('auth.password.broker')->createToken($admin);

    $admin->notify(new \App\Notifications\AdminResetPasswordNotification($token));

    return response()->json(['message' => 'تم إرسال رابط إعادة تعيين كلمة المرور إلى البريد الإلكتروني']);
    }

    public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:admins,email',
        'token' => 'required',
        'password' => 'required|confirmed|min:6',
    ]);

    $admin = Admin::where('email', $request->email)->first();

    // للتحقق من صحة التوكن يمكنك استخدام قاعدة بيانات التوكنات لاحقًا

    $admin->password = Hash::make($request->password);
    $admin->save();

    return response()->json(['message' => 'تم تعيين كلمة مرور جديدة بنجاح']);
}

public function updatePassword(Request $request)
{
    $request->validate([
        'old_password' => 'required',
        'new_password' => 'required|confirmed|min:6',
    ]);

    $admin = auth('admin')->user();

    if (!Hash::check($request->old_password, $admin->password)) {
        return response()->json(['message' => 'كلمة المرور الحالية غير صحيحة'], 403);
    }

    $admin->password = Hash::make($request->new_password);
    $admin->save();

    return response()->json(['message' => 'تم تحديث كلمة المرور بنجاح']);
}
}
