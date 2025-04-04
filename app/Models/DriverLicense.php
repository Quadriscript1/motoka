<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverLicense extends Model {
    use HasFactory;
    protected $table = 'drivers_licenses';

    protected $fillable = [
        'user_id','full_name', 'phone_no', 'address', 'date_of_birth', 
        'license_type', 'passport_photo', 'is_registered'
    ];
    public function user() {
        return $this->belongsTo(User::class);
    }
}

