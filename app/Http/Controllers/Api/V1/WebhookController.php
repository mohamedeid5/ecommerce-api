<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Payment\ProcessWebhookAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        private ProcessWebhookAction $processAction,
    ) {}

    /**
     * Handle payment webhook from provider
     */
    public function payment(Request $request): JsonResponse
    {
        $rawPayload = $request->getContent();
        $signature = $request->header('Stripe-Signature', '');

        $this->processAction->execute($rawPayload, $signature);

        return response()->json(['received' => true], 200);
    }
}
