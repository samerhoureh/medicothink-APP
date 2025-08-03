<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PayClickService;
use App\Models\Payment;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $payClickService;

    public function __construct(PayClickService $payClickService)
    {
        $this->payClickService = $payClickService;
        $this->middleware('auth:sanctum')->except(['payClickWebhook']);
    }

    public function history(Request $request)
    {
        $user = $request->user();
        
        $payments = Payment::where('user_id', $user->id)
            ->with(['subscription.plan'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'payments' => PaymentResource::collection($payments->items()),
                'pagination' => [
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                ],
            ],
        ]);
    }

    public function payclick(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'sometimes|string|size:3',
            'subscription_id' => 'sometimes|exists:subscriptions,id',
            'description' => 'sometimes|string|max:255',
        ]);

        try {
            $user = $request->user();
            
            $result = $this->payClickService->createPayment([
                'user_id' => $user->id,
                'subscription_id' => $request->subscription_id,
                'amount' => $request->amount,
                'currency' => $request->currency ?? 'USD',
                'description' => $request->description ?? 'MedicoThink Payment',
                'metadata' => $request->metadata ?? [],
            ]);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment created successfully',
                    'data' => [
                        'payment_id' => $result['payment_id'],
                        'checkout_url' => $result['checkout_url'],
                    ],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment creation failed',
                    'error' => $result['error'],
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function payClickWebhook(Request $request)
    {
        try {
            $result = $this->payClickService->handleWebhook($request->all());

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook processed successfully',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Webhook processing failed',
                    'error' => $result['error'],
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}