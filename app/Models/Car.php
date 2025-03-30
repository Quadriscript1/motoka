<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Car extends Model
{
    use HasFactory, SoftDeletes;

    // protected $fillable = [
    //     'user_id',
    //     'name_of_owner',
    //     'phone_number',
    //     'address',
    //     'vehicle_make',
    //     'vehicle_model',
    //     'registration_status',    // registered or unregistered
    //     'registration_no',       // for registered cars
    //     'chasis_no',            // required for both
    //     'engine_no',            // required for both
    //     'date_issued',          // for registered cars
    //     'expiry_date',          // for registered cars
    //     'document_images',       // JSON array of image paths for registered cars
    //     'vehicle_year',
    //     'vehicle_color',
    //     'status',               // active, pending, rejected
    // ];
    protected $guarded = [];


    protected $dates = [
        'date_issued',
        'expiry_date'
    ];

    protected $casts = [
        'document_images' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
