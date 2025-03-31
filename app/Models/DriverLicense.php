<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverLicense extends Model {
    use HasFactory;

    protected $fillable = [
        'full_name', 'phone_no', 'address', 'date_of_birth', 
        'license_type', 'passport_photo', 'is_registered'
    ];
}

