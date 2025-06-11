<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MonicreditPaymentController extends Controller
{
    public function initializePayment(Request $request)
    {
        $response = Http::post(env('MONICREDIT_BASE_URL') . '/payment/transactions/init-transaction', [
            'order_id' => $request->order_id,
            'public_key' => env('MONICREDIT_PUBLIC_KEY'),
            'customer' => [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'bvn' => $request->bvn,
                'nin' => $request->nin,
            ],
            'items' => $request->items,
            'currency' => 'NGN',
            'paytype' => 'standard',
        ]);

        return $response->json();
    }

    public function verifyPayment(Request $request)
    {
        $transactionId = $request->transaction_id;
        $privateKey = env('MONICREDIT_PRIVATE_KEY');

        $response = Http::post(env('MONICREDIT_BASE_URL') . '/payment/transactions/verify-transaction', [
            'transaction_id' => $transactionId,
            'private_key' => $privateKey,
        ]);

        dd($response->json());
        return $response->json();
    }
}
