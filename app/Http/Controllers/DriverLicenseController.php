<?php

namespace App\Http\Controllers;

use App\Models\DriverLicense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DriverLicenseController extends Controller
{
    public function store(Request $request)
    {
         //dd($request->all(),auth()->user()->id);
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_no' => 'required|string|max:15',
            'address' => 'required|string',
            'date_of_birth' => 'required|date',
            'license_type' => 'required|in:new,renew',
            'passport_photo' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $passportPath = $request->file('passport_photo')
            ? $request->file('passport_photo')->store('passports', 'public')
            : null;

        $license = DriverLicense::create([
            'user_id' => auth()->user()->id,
            'full_name' => $request->full_name,
            'phone_no' => $request->phone_no,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'license_type' => $request->license_type,
            'passport_photo' => $passportPath,
            'is_registered' => true
        ]);
        return $license;

        return response()->json([
            'message' => 'Driver License application submitted successfully!',
            'data' => $license
        ], 201);
    }

    public function index()
    {
        return response()->json(DriverLicense::all());
    }

    public function show($id)
    {
        $license = DriverLicense::findOrFail($id);
        return response()->json($license);
    }
}
