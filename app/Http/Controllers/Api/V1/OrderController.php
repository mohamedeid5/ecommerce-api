<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Order\PlaceOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\PlaceOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Address;
use App\Models\Cart;
use App\Services\Cart\CartResolver;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        private CartResolver $cartResolver,
        private PlaceOrderAction $placeOrderAction,
    ) {}

    /**
     * Place a new order from the user's cart
     */
    public function store(PlaceOrderRequest $request): JsonResponse
    {
        $user = $request->user();
        $cart = $this->cartResolver->resolve()->load('items.product');
        $address = Address::findOrFail($request->validated('address_id'));

        $order = $this->placeOrderAction->execute(
            user: $user,
            cart: $cart,
            address: $address,
            customerNotes: $request->validated('customer_notes'),
        );

        return response()->json([
            'message' => 'Order placed successfully',
            'data' => new OrderResource($order),
        ], 201);
    }
}
