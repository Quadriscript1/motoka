<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plate_number',
        'type',
        'preferred_name',
        'full_name',
        'address',
        'chassis_number',
        'engine_number',
        'phone_number',
        'colour',
        'car_make',
        'car_type',
        'business_type',
        'cac_document',
        'letterhead',
        'means_of_identification'
    ];
}

