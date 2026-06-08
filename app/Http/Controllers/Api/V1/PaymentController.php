<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Payment\InitiatePaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private InitiatePaymentAction $initiateAction,
    ) {}

    /**
     * Initiate a payment for an order
     */
    public function initiate(Request $request, Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $result = $this->initiateAction->execute($order);

        return response()->json([
            'message' => 'Payment initiated',
            'data' => [
                'payment' => new PaymentResource($result['payment']),
                'payment_url' => $result['payment_url'],
                'expires_at' => $result['expires_at'],
            ],
        ], 201);
    }
}
