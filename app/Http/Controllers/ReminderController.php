<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReminderController extends Controller
{
    // Fetch a single reminder for a specific car
    public function index(Request $request, $carId)
    {
        // Get the authenticated user
        $userId = Auth::user()->userId;

        // Fetch the reminder for the specific car
        $reminder = Reminder::where('user_id', $userId)
            ->where('ref_id', $carId) // Assuming ref_id is the car ID
            ->where('is_sent', false) // Optionally filter out sent reminders
            ->first();

        if ($reminder) {
            return response()->json([
                'status' => 'success',
                'data' => $reminder,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'No reminder found for this car.',
        ], 404);
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
