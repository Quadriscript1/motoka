<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onUpdate('cascade')->onDelete('restrict');
        $table->string('transaction_id')->unique();
        $table->double('amount', 15, 2);
        $table->foreignId('payment_schedule_id')->constrained('payment_schedules')->onUpdate('cascade')->onDelete('restrict');
        $table->enum('status',['pending','approved','declined'])->default('pending');
        $table->string('reference_code')->nullable();
        $table->string('payment_description')->nullable();
        $table->json('raw_response')->nullable();
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
