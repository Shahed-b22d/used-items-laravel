<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Store;

class StoreAuthController extends Controller
{
    public function register(Request $request)
    {
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

        $imagePath = $request->file('commercial_record')->store('commercial_records', 'public');

        $store = Store::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'commercial_record' => $imagePath,
            'is_approved' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم استلام طلب التسجيل وسيتم مراجعته من قبل الإدارة',
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
