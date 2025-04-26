<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved successfully',
            'data' => $user
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,' . $user->id,
            'address' => 'string|max:255',
            'gender' => 'in:male,female,other',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name', 'email', 'address', 'gender']);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('profile_images', 'public');
            $data['image'] = $imagePath;
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Old password is incorrect',
                // 'data' => null
            ], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
            // 'data' => null
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $user = Auth::user();

        // Confirm deletion by requiring password re-entry
        $request->validate([
            'password' => 'required',
        ]);

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect',
                // 'data' => null
            ], 400);
        }

        // Check for dependencies (e.g., posts, comments)
        // Example: if ($user->posts()->exists()) { ... }

        // Log the deletion
        \Log::info('User account deleted', ['user_id' => $user->id]);

        // Notify the user (e.g., send an email)
        // Mail::to($user->email)->send(new AccountDeletedMail());

        // Delete the user
        $user->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully',
            // 'data' => null
        ]);
    }
}
