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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $imagePath = $request->file('commercial_record')->store('commercial_records', 'public');

        $store = Store::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'commercial_record' => $imagePath,
            'is_approved' => false, // هذا الحقل يحدد أنه بانتظار الموافقة
        ]);

        $token = $store->createToken('store_token')->plainTextToken;

        return response()->json(['token' => $token, 'store' => $store], 201);
    }


    public function login(Request $request)
    {
        $store = Store::where('email', $request->email)->first();

        if (!$store || !Hash::check($request->password, $store->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$store->is_approved) {
            return response()->json([
                'message' => 'Your account is under review. Please wait for admin approval.'
            ], 403);
        }

        $token = $store->createToken('store_token')->plainTextToken;

        return response()->json(['token' => $token, 'store' => $store]);
    }



public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();
    return response()->json(['message' => 'Logged out successfully']);
}


}
