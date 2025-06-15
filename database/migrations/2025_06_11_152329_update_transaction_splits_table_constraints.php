<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transaction_splits', function (Blueprint $table) {
           
            $table->dropUnique(['transaction_id']);
            
           
            $table->unique(['transaction_id', 'revenue_head_id', 'settlement_bank_id'], 'transaction_splits_unique');
        });
    }
    
    public function down()
    {
        Schema::table('transaction_splits', function (Blueprint $table) {
            $table->dropUnique('transaction_splits_unique');
            $table->unique('transaction_id');
        });
    }
};
