<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => [
                    'id' => $user->id,
                    'user_type_id' => $user->user_type_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'image' => $user->image ? asset('storage/' . $user->image) : null,
                    'phone_number' => $user->phone_number,
                    'social_id' => $user->social_id,
                    'social_type' => $user->social_type,
                    'social_avatar' => $user->social_avatar,
                    'address' => $user->address,
                    'gender' => $user->gender,
                    'email_verification_code' => $user->email_verification_code,
                    'email_verification_code_expires_at' => $user->email_verification_code_expires_at,
                    'phone_verification_code' => $user->phone_verification_code,
                    'phone_verification_code_expires_at' => $user->phone_verification_code_expires_at,
                    'phone_verified_at' => $user->phone_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'deleted_at' => $user->deleted_at,
                    'user_type' => $user->userType ? $user->userType->user_type_name : null
                ]
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Profile Not found',
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'address' => 'sometimes|string|max:255',
            'gender' => ['sometimes', 'string', function ($attribute, $value, $fail) {
                if (!in_array(strtolower($value), ['male', 'female', 'other'])) {
                    $fail('The gender must be Male, Female, or Other.');
                }
            }],
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name', 'email', 'address']);
        
        // Convert gender to lowercase before saving
        if ($request->has('gender')) {
            $data['gender'] = strtolower($request->gender);
        }

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($user->image) {
                Storage::disk('public')->delete($user->image);
            }
            
            $imagePath = $request->file('image')->store('profile_images', 'public');
            $data['image'] = $imagePath;
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'id' => $user->id,
                'user_type_id' => $user->user_type_id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'image' => $user->image ? asset('storage/' . $user->image) : null,
                'phone_number' => $user->phone_number,
                'social_id' => $user->social_id,
                'social_type' => $user->social_type,
                'social_avatar' => $user->social_avatar,
                'address' => $user->address,
                'gender' => $user->gender,
                'email_verification_code' => $user->email_verification_code,
                'email_verification_code_expires_at' => $user->email_verification_code_expires_at,
                'phone_verification_code' => $user->phone_verification_code,
                'phone_verification_code_expires_at' => $user->phone_verification_code_expires_at,
                'phone_verified_at' => $user->phone_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'deleted_at' => $user->deleted_at,
                'user_type' => $user->userType ? $user->userType->user_type_name : null
            ]
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
            ], 400);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
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
            ], 400);
        }

        // Log the deletion
        Log::info('User account deleted', ['user_id' => $user->id]);

        // Soft delete the user
        $user->update(['deleted_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully',
        ]);
    }

    public function restoreAccount(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Find the soft-deleted user
        $user = User::withTrashed()->where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Check the password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect',
            ], 400);
        }

        // Restore the user
        $user->update(['deleted_at' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Account restored successfully',
            'data' => $user,
        ]);
    }
}
