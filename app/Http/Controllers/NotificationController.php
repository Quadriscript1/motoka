<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        // Get the authenticated user
        $userId = Auth::user()->userId;

        // Fetch notifications for the user
        $notifications = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $notifications,
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Notification::find($id);
        if ($notification) {
            $notification->is_read = true;
            $notification->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Notification marked as read.',
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Notification not found.'], 404);
    }
}
