<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
    }

    /**
     * Register a new car
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_of_owner' => 'required|string|max:255',
            'address' => 'required|string',
            'vehicle_make' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'registration_status' => 'required|in:registered,unregistered',
            'document_images.*' => 'required_if:registration_status,registered|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle document images upload
        $documentImages = [];
        if ($request->hasFile('document_images')) {
            foreach ($request->file('document_images') as $image) {
                $path = $image->store('car-documents', 'public');
                $documentImages[] = $path;
            }
        }

        try {
            $car = Car::create([
                'user_id' => auth('api')->id(),
                'name_of_owner' => $request->name_of_owner,
                'address' => $request->address,
                'vehicle_make' => $request->vehicle_make,
                'vehicle_model' => $request->vehicle_model,
                'registration_status' => $request->registration_status,
                'document_images' => $documentImages,
                'status' => $request->registration_status === 'registered' ? 'pending' : 'active'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Car registered successfully',
                'car' => $car
            ]);

        } catch (\Exception $e) {
            // Clean up uploaded files if car creation fails
            foreach ($documentImages as $path) {
                Storage::disk('public')->delete($path);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to register car',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's cars
     */
    public function getMyCars()
    {
        $cars = Car::where('user_id', auth('api')->id())
                   ->orderBy('created_at', 'desc')
                   ->get();

        return response()->json([
            'status' => 'success',
            'cars' => $cars
        ]);
    }

    /**
     * Get specific car details
     */
    public function show($id)
    {
        $car = Car::where('user_id', auth('api')->id())
                  ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'car' => $car
        ]);
    }

    /**
     * Update car details
     */
    public function update(Request $request, $id)
    {
        $car = Car::where('user_id', auth('api')->id())
                  ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name_of_owner' => 'sometimes|string|max:255',
            'address' => 'sometimes|string',
            'vehicle_make' => 'sometimes|string|max:255',
            'vehicle_model' => 'sometimes|string|max:255',
            'document_images.*' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle new document images
        if ($request->hasFile('document_images')) {
            $documentImages = $car->document_images ?? [];
            
            foreach ($request->file('document_images') as $image) {
                $path = $image->store('car-documents', 'public');
                $documentImages[] = $path;
            }
            
            $car->document_images = $documentImages;
        }

        $car->update($request->only([
            'name_of_owner',
            'address',
            'vehicle_make',
            'vehicle_model'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Car updated successfully',
            'car' => $car
        ]);
    }

    /**
     * Delete a car
     */
    public function destroy($id)
    {
        $car = Car::where('user_id', auth('api')->id())
                  ->findOrFail($id);

        // Delete associated documents
        if (!empty($car->document_images)) {
            foreach ($car->document_images as $path) {
                Storage::disk('public')->delete($path);
            }
        }

        $car->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Car deleted successfully'
        ]);
    }
}
