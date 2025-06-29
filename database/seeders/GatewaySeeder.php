<?php

namespace Database\Seeders;

use App\Models\Gateway;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user_types = array(
            ['id' => 1, 'gateway_name' => "MONICREDIT",'gateway_slug'=>"MONICREDIT",'merchant_id'=>"MC1618764",'PUB_KEY'=>"PUB_LIVE_AC64A7D43F1E2BB",'PRI_KEY'=>"PRI_LIVE_AC64A7D43F1E2BF",'hash_type'=>"sha256",'status' => "active", 'created_at' => now()],
        );

        foreach ($user_types as $type) {
            Gateway::updateOrCreate(
            [
                'id' => $type['id'],
                'gateway_name' => $type['gateway_name'],
                'gateway_slug' => $type['gateway_slug'],
            ], 
            [ 
            'merchant_id' => $type['merchant_id'],
            'PUB_KEY' => $type['PUB_KEY'],
            'PRI_KEY' => $type['PRI_KEY'],
            'hash_type' => $type['hash_type'],
            'status' => $type['status'],
            'created_at' => $type['created_at'],
            'updated_at' => now(),
            ]
            );
        }
    }
}
