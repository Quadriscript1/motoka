<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number')->nullable()->unique()->after('email');
            $table->string('social_id')->nullable()->after('phone_number');
            $table->string('social_type')->nullable()->after('social_id');
            $table->string('avatar')->nullable()->after('social_type');
            // Make email nullable since users can use phone number instead
            $table->string('email')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'social_id', 'social_type', 'avatar']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
