<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\DeliveryAgent;

class DeliveryAgentAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $agent = DeliveryAgent::where('email', $request->email)->first();

        if (!$agent || !Hash::check($request->password, $agent->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // حذف التوكنات القديمة (اختياري)
        $agent->tokens()->delete();

        // إنشاء توكن جديد
        $token = $agent->createToken('delivery-agent-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'agent' => [
                'name' => $agent->name,
                'username' => $agent->username,
                'phone' => $agent->phone,
                'email' => $agent->email,
                'location' => $agent->location,
            ]
        ]);
    }

    public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Logged out successfully'
    ]);
}

public function updateProfile(Request $request)
{
    $user = $request->user(); // المندوب الحالي

    $request->validate([
        'name' => 'nullable|string|max:255',
        'username' => 'nullable|string|max:255|unique:delivery_agents,username,' . $user->id,
        'email' => 'nullable|email|unique:delivery_agents,email,' . $user->id,
        'phone' => 'nullable|string|max:20',
        'location' => 'nullable|string|max:255',
        'password' => 'nullable|string|min:6|confirmed',
    ]);

    // تحديث الحقول
    $user->update([
        'name' => $request->name ?? $user->name,
        'username' => $request->username ?? $user->username,
        'email' => $request->email ?? $user->email,
        'phone' => $request->phone ?? $user->phone,
        'location' => $request->location ?? $user->location,
        'password' => $request->password ? Hash::make($request->password) : $user->password,
    ]);

    return response()->json([
        'message' => 'Profile updated successfully',
        'agent' => $user
    ]);
}
}
