<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Notifications\DeliveryAgentResetPasswordNotification;

class DeliveryAgentPasswordController extends Controller
{
    // تغيير كلمة المرور عند تسجيل الدخول
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $agent = Auth::user(); // أو auth('sanctum')->user()

        if (!$agent || !($agent instanceof DeliveryAgent)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!Hash::check($request->old_password, $agent->password)) {
            return response()->json(['message' => 'Old password is incorrect'], 400);
        }

        $agent->password = Hash::make($request->new_password);
        $agent->save();

        return response()->json(['message' => 'Password changed successfully']);
    }

    // إرسال رمز تأكيد عند النسيان
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:delivery_agents,email'
        ]);

        $agent = DeliveryAgent::where('email', $request->email)->first();

        DB::table('password_reset_tokens')->where('email', $agent->email)->delete();

        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email' => $agent->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        $agent->notify(new DeliveryAgentResetPasswordNotification($token));

        return response()->json(['message' => 'تم إرسال رمز إعادة تعيين كلمة المرور إلى بريدك الإلكتروني']);
    }

    // تعيين كلمة مرور جديدة باستخدام الرمز
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:delivery_agents,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$passwordReset || Carbon::parse($passwordReset->created_at)->addMinutes(60)->isPast()) {
            return response()->json(['message' => 'رمز غير صالح أو منتهي الصلاحية'], 400);
        }

        $agent = DeliveryAgent::where('email', $request->email)->first();
        $agent->password = Hash::make($request->password);
        $agent->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'تم تعيين كلمة المرور الجديدة بنجاح']);
    }
}
