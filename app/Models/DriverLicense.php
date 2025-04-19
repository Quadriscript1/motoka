<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverLicense extends Model {
    use HasFactory;
    protected $table = 'drivers_licenses'; // plural!

    protected $guarded = [];
    public function user() {
        return $this->belongsTo(User::class);
    }
}

