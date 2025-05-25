<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReminderController extends Controller
{
    public function index(Request $request)
    {
        // Get the authenticated user
        $userId = Auth::user()->userId;

        // Fetch reminders for the user
        $reminders = Reminder::where('user_id', $userId)
            ->where('is_sent', false) // Optionally filter out sent reminders
            ->orderBy('remind_at', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $reminders,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'message' => 'required|string',
            'remind_at' => 'required|date',
        ]);

        $reminder = Reminder::create([
            'user_id' => Auth::user()->userId,
            'type' => $request->type,
            'message' => $request->message,
            'remind_at' => $request->remind_at,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $reminder,
        ], 201);
    }
}
