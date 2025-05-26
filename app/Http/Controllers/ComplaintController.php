<?php

// app/Http/Controllers/ComplaintController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Complaint;

class ComplaintController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        // فقط users أو stores يمكنهم إنشاء شكوى
        if (!in_array($request->actor_guard, ['user', 'store'])) {
            return response()->json(['error' => 'غير مسموح لك بإرسال شكوى'], 403);
        }

        $complaint = $request->actor->complaints()->create([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'تم إرسال الشكوى بنجاح',
            'data' => $complaint
        ], 201);
    }

    public function index(Request $request)
    {
        // فقط الادمن يمكنه مشاهدة الشكاوى
        if ($request->actor_guard !== 'admin') {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        $complaints = Complaint::with('complainable')->get();

        return response()->json($complaints);
    }
}
