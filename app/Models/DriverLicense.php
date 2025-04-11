<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverLicense extends Model {
    use HasFactory;
<<<<<<< HEAD
    protected $table = 'drivers_licenses'; // plural!

    protected $fillable = [
        'user_id',
        'license_number',
        'license_type',
        'full_name',
        'phone_number',
        'address',
        'date_of_birth',
        'place_of_birth',
        'state_of_origin',
        'local_government',
        'blood_group',
        'height',
        'eye_color',
        'occupation',
        'next_of_kin',
        'next_of_kin_phone',
        'mother_maiden_name',
        'validity_years',
        'issued_date',
        'expiry_date',
=======
    protected $table = 'drivers_licenses';

    protected $fillable = [
        'user_id','full_name', 'phone_no', 'address', 'date_of_birth', 
        'license_type', 'passport_photo', 'is_registered'
>>>>>>> main
    ];
    public function user() {
        return $this->belongsTo(User::class);
    }
}

