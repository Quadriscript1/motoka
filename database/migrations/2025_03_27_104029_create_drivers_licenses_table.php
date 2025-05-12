<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('drivers_licenses', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('license_number')->unique()->nullable();
            $table->enum('license_type', ['new', 'renew']);; 
            $table->string('full_name');
            $table->string('phone_number');
            $table->text('address');
            $table->date('date_of_birth');
            $table->string('place_of_birth')->nullable();
            $table->string('state_of_origin');
            $table->string('local_government');
            $table->string('blood_group')->nullable();
            $table->string('height')->nullable();
            $table->string('eye_color')->nullable();
            $table->string('occupation')->nullable();
            $table->string('next_of_kin')->nullable();
            $table->string('next_of_kin_phone')->nullable();
            $table->string("mother_maiden_name")->nullable();
            $table->integer('validity_years')->nullable(); // e.g., 5 years
            $table->string('passport_photo')->nullable();
            $table->date('issued_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers_licenses');
    }
};

// return new class extends Migration {
//     public function up(): void {
//         Schema::create('drivers_licenses', function (Blueprint $table) {
//             $table->id();
//             $table->string('full_name');
//             $table->string('phone_no');
//             $table->text('address');
//             $table->date('date_of_birth');
//             $table->enum('license_type', ['new', 'renew']);
//             $table->string('passport_photo')->nullable();
//             $table->boolean('is_registered')->default(false);
//             $table->timestamps();
//         });
//     }

//     public function down(): void {
//         Schema::dropIfExists('drivers_licenses');
//     }
// };

