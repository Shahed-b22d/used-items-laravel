<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;

class UserPasswordController extends Controller
{
    // ✅ تعديل كلمة السر (للمستخدم المسجل)
    public function change(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 401);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully']);
    }

    // ✅ إرسال رمز إعادة التعيين إلى الإيميل
public function sendResetLink(Request $request)
{
    $request->validate(['email' => 'required|email']);

    // 👇 نحدد صراحةً أن البروكر اسمه 'users'
    $status = Password::broker('users')->sendResetLink(
        $request->only('email')
    );

    if ($status !== Password::RESET_LINK_SENT) {
        return response()->json([
            'message' => 'Unable to send reset link.',
            'debug_status' => $status,
        ], 400);
    }

    return response()->json(['message' => 'Reset link sent to your email.']);
}

    // ✅ تعيين كلمة مرور جديدة باستخدام الرمز
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password has been reset.'])
            : response()->json(['message' => 'Invalid token or email.'], 400);
    }
}

