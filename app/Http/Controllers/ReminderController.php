<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReminderController extends Controller
{
    // Fetch a single reminder for a specific car
    public function index(Request $request)
    {
        // Get the authenticated user
        $userId = Auth::user()->userId;

        // Fetch the most recent reminder for expired cars
        $reminder = Reminder::where('user_id', $userId)
            ->where('is_sent', false) 
            ->orderBy('created_at', 'desc')
            ->first(); 

        if (!$reminder) {
            return response()->json([
                'status' => 'success',
                'message' => 'No reminders found.',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $reminder, 
        ]);
    }

    // Update an existing reminder
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'message' => 'required|string',
            'remind_at' => 'required|date',
            'ref_id' => 'required|integer', // Ensure ref_id is provided
        ]);

        // Find the existing reminder
        $reminder = Reminder::where('user_id', Auth::user()->userId)
            ->where('ref_id', $request->ref_id)
            ->first();

        if ($reminder) {
            // Update the existing reminder
            $reminder->update([
                'type' => $request->type,
                'message' => $request->message,
                'remind_at' => $request->remind_at,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $reminder,
            ]);
        }

        // If no reminder exists, create a new one (optional)
        $reminder = Reminder::create([
            'user_id' => Auth::user()->userId,
            'type' => $request->type,
            'message' => $request->message,
            'remind_at' => $request->remind_at,
            'ref_id' => $request->ref_id,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $reminder,
        ], 201);
    }
}
