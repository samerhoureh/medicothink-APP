<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function processStripePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'payment_method_id' => 'required|string',
            'plan_id' => 'required|string|in:basic,premium,professional',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        
        $result = $this->paymentService->processStripePayment(
            $request->amount,
            $request->currency,
            $request->payment_method_id,
            $user->id
        );

        if ($result['success']) {
            $subscription = $this->paymentService->createSubscription(
                $user->id,
                $request->plan_id,
                $result
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'transaction' => $result['transaction'],
                'subscription' => $subscription,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment failed',
            'error' => $result['error'] ?? 'Unknown error',
        ], 400);
    }

    public function processPayPalPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|string',
            'payer_id' => 'required|string',
            'plan_id' => 'required|string|in:basic,premium,professional',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        
        $result = $this->paymentService->processPayPalPayment(
            $request->payment_id,
            $request->payer_id,
            $user->id
        );

        if ($result['success']) {
            $subscription = $this->paymentService->createSubscription(
                $user->id,
                $request->plan_id,
                $result
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'transaction' => $result['transaction'],
                'subscription' => $subscription,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment failed',
            'error' => $result['error'] ?? 'Unknown error',
        ], 400);
    }

    public function getPaymentHistory()
    {
        $user = auth()->user();
        
        $transactions = $user->paymentTransactions()
            ->with('subscription')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'transactions' => $transactions,
        ]);
    }
}