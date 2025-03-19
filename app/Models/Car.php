<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Car extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name_of_owner',
        'address',
        'vehicle_make',
        'vehicle_model',
        'registration_status', // registered or unregistered
        'document_images',     // JSON array of image paths
        'status',             // active, pending, rejected
    ];

    protected $casts = [
        'document_images' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
