<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Notifications\AdminResetPasswordNotification;
use Carbon\Carbon;


class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('username', $request->username)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات الدخول غير صحيحة'
            ], 401);
        }

        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'message' => 'تم تسجيل الدخول بنجاح'
        ]);
    }
// تسجيل خروج الأدمن
    public function logout(Request $request)
    {
        $admin = Auth::user(); // أو auth('sanctum')->user()

        if (!$admin || !($admin instanceof Admin)) {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح'
            ], 403);
        }

        // حذف جميع التوكنات للأدمن الحالي
        $admin->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم تسجيل الخروج بنجاح'
        ]);
    }

}
