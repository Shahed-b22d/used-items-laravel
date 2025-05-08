<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Store;
use Illuminate\Http\Request;

class UserStoreController extends Controller
{
     // ✅ عرض جميع المستخدمين
    public function getUsers()
    {
        return response()->json(User::all());
    }

    // ✅ عرض جميع المتاجر
    public function getStores()
    {
        return response()->json(Store::all());
    }

    // ✅ حذف مستخدم
    public function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted']);
    }

    // ✅ حذف متجر
    public function deleteStore($id)
    {
        $store = Store::find($id);

        if (!$store) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        $store->delete();

        return response()->json(['message' => 'Store deleted']);
    }
}
