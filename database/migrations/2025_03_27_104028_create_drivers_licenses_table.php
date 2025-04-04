<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('drivers_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->string('phone_no');
            $table->text('address');
            $table->date('date_of_birth');
            $table->enum('license_type', ['new', 'renew']);
            $table->string('passport_photo')->nullable();
            $table->boolean('is_registered')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('drivers_licenses');
    }
};

