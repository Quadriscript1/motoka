<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionSplitsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('transaction_splits', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id');
            $table->string('revenue_head_id'); 
            $table->string('settlement_bank_id'); 
            $table->string('account_id'); 
            $table->decimal('percent', 5, 2); 
            $table->string('settlement_batch'); 
            $table->decimal('split_amount', 15, 2); 
            $table->string('settlement'); 
            $table->timestamps();
            
            // Create a composite unique constraint
            $table->unique(['transaction_id', 'revenue_head_id', 'settlement_bank_id'], 'transaction_splits_unique');
            
            // Add individual indexes for better query performance
            $table->index('transaction_id');
            $table->index('revenue_head_id');
            $table->index('settlement_bank_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('transaction_splits');
    }
}