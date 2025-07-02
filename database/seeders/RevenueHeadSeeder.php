<?php

namespace Database\Seeders;

use App\Models\PaymentHead;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RevenueHeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run()
	{
		$payment_revenue_heads = array(
			array('id' => '1', 'revenue_head_name' => 'MOTOKA PAYMENT', 'revenue_head_code' => 'REV686003f87e350', 'bank_id' => '1', 'gateway_id' => '1', 'fee_bearer' => 'merchant', 'status' => 'active', 'created_at' => '2025-06-28 16:49:13', 'updated_at' => '2025-06-28 16:49:13'),
		);

		DB::table('revenue_heads')->insert($payment_revenue_heads);
	}
}
