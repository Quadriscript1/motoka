<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentSchedule;
// use Faker\Provider\ar_EG\Payment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
  public function initializePayment(Request $request)
{
     $user= Auth::user();

    $transaction_id = Str::random(10);

    $getPaymentSchedule = PaymentSchedule::where('id', $request->payment_schedule_id)->first();


    $items = [
        (object)[
            'unit_cost' => $getPaymentSchedule->amount,
            'item' => $getPaymentSchedule->payment_head->payment_head_name . " " . $getPaymentSchedule->revenue_head->revenue_head,
            'revenue_head_code' => $getPaymentSchedule->revenue_head->revenue_head_code,
        ]
    ];

    $response = Http::post(env('MONICREDIT_BASE_URL') . '/payment/transactions/init-transaction', [
        'order_id' => $transaction_id,
        'public_key' => env('MONICREDIT_PUBLIC_KEY'),
        'customer' => [
            'first_name' => $user->name,
            'last_name' => "",
            'email' => $user->email,
            'phone' => $user->phone,
        ],
        "fee_bearer"=> "client",
        'items' => $items,
        'currency' => 'NGN',
        'paytype' => 'standard',
    ]);
    $data = $response->json();

    $save = Payment::create([
        'transaction_id' => $transaction_id,
        'amount' => $getPaymentSchedule->amount,
        'payment_schedule_id' => $request->payment_schedule_id,
        'status' => 'pending',
        'reference_code' => $data['id'],
        'payment_description' => $items[0]->item, 
        'user_id' => $user->id, 
        'raw_response' => $response->json(),
    ]);
    return response()->json([
        'message' => 'Payment initialized successfully',
        'data' => $data
    ]);
}

public function verifyPayment($transaction_id)
{

    // dd($transaction_id);
    // Call Monicredit verification API
    $response = Http::post(env('MONICREDIT_BASE_URL') . "/payment/transactions/verify-transaction", [
        'transaction_id' => $transaction_id,
        'private_key' => env('MONICREDIT_PRIVATE_KEY')
    ]);

    if (!$response->ok()) {
        return response()->json(['message' => 'Verification failed'], 500);
    }

    $data = $response->json();

    // Check payment status
    if (isset($data['status']) && $data['status'] == true) {
        // Find payment by order_id or reference_code
        $payment = Payment::where('transaction_id', $data['orderid'])->first();

        if (!$payment) {
            return response()->json(['message' => 'Payment record not found'], 404);
        }

        // Update payment status
        $payment->update([
            'status' => strtolower($data['data']['status']),
            'raw_response' => $data
        ]);

        return response()->json([
            'message' => 'Payment verified successfully',
            'data' => $data
        ]);
    }

    return response()->json([
        'message' => 'Payment not successful',
        'data' => $data
    ]);
}



  
}
