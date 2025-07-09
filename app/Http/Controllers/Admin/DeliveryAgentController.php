<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryAgent;
use Illuminate\Support\Facades\Hash;

class DeliveryAgentController extends Controller
{
    // عرض جميع المندوبين
    public function index()
    {
        return response()->json(DeliveryAgent::all(), 200);
    }

    // إضافة مندوب
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'username' => 'required|string|unique:delivery_agents,username',
            'phone' => 'required|unique:delivery_agents,phone',
            'email' => 'nullable|email|unique:delivery_agents,email',
            'password' => 'required|string|min:6',
            'location' => 'nullable|string',
        ]);

        // تشفير كلمة المرور قبل الحفظ
        $validated['password'] = Hash::make($validated['password']);

        $agent = DeliveryAgent::create($validated);

        return response()->json(['message' => 'Delivery agent created', 'data' => $agent], 201);
    }

    // تعديل بيانات مندوب
    public function update(Request $request, $id)
    {
        $agent = DeliveryAgent::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'username' => 'sometimes|string|unique:delivery_agents,username,' . $id,
            'phone' => 'sometimes|unique:delivery_agents,phone,' . $id,
            'email' => 'nullable|email|unique:delivery_agents,email,' . $id,
            'password' => 'sometimes|string|min:6',
            'location' => 'sometimes|string',
        ]);

        // إذا تم تمرير كلمة مرور، تشفيرها قبل الحفظ
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $agent->update($validated);

        return response()->json(['message' => 'Delivery agent updated', 'data' => $agent], 200);
    }

    // حذف مندوب
    public function destroy($id)
    {
        $agent = DeliveryAgent::findOrFail($id);
        $agent->delete();

        return response()->json(['message' => 'Delivery agent deleted'], 200);
    }
}
