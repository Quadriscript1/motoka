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
            'license_number' => 'nullable|unique:licenses',
            'license_type' => 'nullable',
            'full_name' => 'nullable',
            'phone_number' => 'nullable',
            'address' => 'nullable',
            'date_of_birth' => 'nullable|date',
            'state_of_origin' => 'nullable',
            'local_government' => 'nullable',
            'validity_years' => 'nullable|integer',
            'place_of_birth'=>'nullable',
            'blood_group'=>'nullable',
            'height'=>'nullable',
            'eye_color'=>'nullable',
            'occupation'=>'nullable',
            'next_of_kin'=>'nullable',
            'next_of_kin_phone'=>'nullable',
            'mother_maiden_name'=>'nullable',
            'issued_date' => 'required|date',
            'expiry_date' => 'required|date|after:issued_date',
            'passport_photo' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);
        

        $passportPath = $request->file('passport_photo')
            ? $request->file('passport_photo')->store('passports', 'public')
            : null;

        $license = DriverLicense::create([
            'user_id' => auth()->user()->id,
            'full_name' => $request->full_name,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'license_type' => $request->license_type,
            'license_number' => $request->license_number,
            'state_of_origin' => $request->state_of_origin,
            'local_government' => $request->local_government,
            'validity_years' => $request->validity_years,
            'place_of_birth' => $request->place_of_birth,
            'blood_group' => $request->blood_group,
            'height' => $request->height,
            'eye_color' => $request->eye_color,
            'occupation' => $request->occupation,
            'next_of_kin' => $request->next_of_kin,
            'next_of_kin_phone' => $request->next_of_kin_phone,
            'mother_maiden_name' => $request->mother_maiden_name,
            'issued_date' => $request->issued_date,
            'expiry_date' => $request->expiry_date,
            'passport_photo' => $passportPath
            
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
