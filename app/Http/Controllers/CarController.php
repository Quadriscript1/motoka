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
    // public function __construct()
    // {
    //     parent::__construct();
    //     $this->middleware('auth:api');
    // }

    /**
     * Register a new car
     */
    public function register(Request $request)
    {
        // Base validation rules for both registered and unregistered cars
        $baseRules = [
            'name_of_owner' => 'required|string|max:255',
            'phone_number' => 'required|string',
            'address' => 'required|string',
            'vehicle_make' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'registration_status' => 'required|in:registered,unregistered',
            'chasis_no' => 'nullable|string|nullable',
            'engine_no' => 'nullable|string|nullable',
            'vehicle_year' => 'required|integer|digits:4|min:1900|max:' . (date('Y') + 1),
            'vehicle_color' => 'required|string|max:50'
        ];

        // Additional rules for registered cars
        $registeredRules = [
            'registration_no' => 'nullable|string|nullable',
            'date_issued' => 'nullable|date|nullable',
            'expiry_date' => 'nullable|date|after:date_issued|nullable',
            'document_images.*' => 'nullable |image|mimes:jpeg,png,jpg|max:2048',
        ];

        // Apply validation rules based on registration status
        $rules = $baseRules;
        if ($request->registration_status === 'registered') {
            $rules = array_merge($baseRules, $registeredRules);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        $existingCar = Car::where(function ($query) use ($request) {
            if ($request->registration_status === 'registered') {
                $query->where('registration_no', $request->registration_no);
            }
            $query->orWhere('chasis_no', $request->chasis_no)
                  ->orWhere('engine_no', $request->engine_no);
        })->first();
    
        if ($existingCar) {
            return response()->json([
                'status' => 'error',
                'message' => 'A car with the same registration number, chassis number, or engine number already exists.',
            ], 409); // Conflict status code
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
            $carData = [
                'user_id' => auth()->user()->id,
                'name_of_owner' => $request->name_of_owner,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'vehicle_make' => $request->vehicle_make,
                'vehicle_model' => $request->vehicle_model,
                'registration_status' => $request->registration_status,
                'chasis_no' => $request->chasis_no,
                'engine_no' => $request->engine_no,
                'vehicle_year' => $request->vehicle_year,
                'vehicle_color' => $request->vehicle_color,
                'status' => $request->registration_status === 'registered' ? 'pending' : 'active'
            ];

            // Add registered car specific fields
            if ($request->registration_status === 'registered') {
                $carData = array_merge($carData, [
                    'registration_no' => $request->registration_no,
                    'date_issued' => $request->date_issued,
                    'expiry_date' => $request->expiry_date,
                    'document_images' => $documentImages,
                ]);
            }

            $car = Car::create($carData);

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
        $cars = Car::where('user_id', auth()->user()->id)
                   ->orderBy('created_at', 'asc')
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
        $car = Car::where('user_id', auth()->user()->id)
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
        $car = Car::where('user_id', auth()->user()->id)
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
        $car = Car::where('user_id', auth()->user()->id)
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
