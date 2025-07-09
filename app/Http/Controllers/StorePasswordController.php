<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Notifications\StoreResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class StorePasswordController extends Controller
{
    // ✅ تغيير كلمة المرور
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $store = Auth::user();

        if (!$store || !($store instanceof Store)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!Hash::check($request->old_password, $store->password)) {
            return response()->json(['message' => 'Old password is incorrect'], 400);
        }

        $store->password = Hash::make($request->new_password);
        $store->save();

        return response()->json(['message' => 'Password changed successfully']);
    }

    // ✅ إرسال رمز التحقق عند نسيان كلمة المرور
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:stores,email'
        ]);

        $store = Store::where('email', $request->email)->first();

        DB::table('password_reset_tokens')->where('email', $store->email)->delete();

        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email' => $store->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        $store->notify(new StoreResetPasswordNotification($token));

        return response()->json(['message' => 'Reset token sent to your email']);
    }

    // ✅ تعيين كلمة مرور جديدة
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:stores,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$tokenData) {
            return response()->json(['message' => 'Invalid token'], 400);
        }

        if (Carbon::parse($tokenData->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'Token expired'], 400);
        }

        $store = Store::where('email', $request->email)->first();
        $store->password = Hash::make($request->password);
        $store->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password has been reset successfully']);
    }
}
