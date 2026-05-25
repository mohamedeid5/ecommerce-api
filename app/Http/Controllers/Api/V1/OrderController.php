<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Order\CancelOrderAction;
use App\Actions\Order\ListUserOrdersAction;
use App\Actions\Order\PlaceOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\PlaceOrderRequest;
use App\Http\Resources\OrderListResource;
use App\Http\Resources\OrderResource;
use App\Models\Address;
use App\Models\Order;
use App\Services\Cart\CartResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(
        private CartResolver $cartResolver,
        private PlaceOrderAction $placeOrderAction,
        private ListUserOrdersAction $listOrdersAction,
        private CancelOrderAction $cancelOrderAction,
    ) {}

    /**
     * List user's orders with pagination
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $this->listOrdersAction->execute(
            user: $request->user(),
            perPage: $request->integer('per_page', 10),
            statusFilter: $request->string('status')->toString() ?: null,
        );

        return OrderListResource::collection($orders);
    }

    /**
     * Show a specific order
     */
    public function show(Request $request, Order $order): OrderResource
    {
        $this->authorize('view', $order);

        $order->load(['items', 'statusHistory.changedBy']);

        return new OrderResource($order);
    }

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

    /**
     * Cancel an order
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        $this->authorize('cancel', $order);

        $cancelled = $this->cancelOrderAction->execute(
            order: $order,
            cancelledBy: $request->user(),
            reason: $request->input('reason'),
        );

        return response()->json([
            'message' => 'Order cancelled successfully',
            'data' => new OrderResource($cancelled),
        ]);
    }
}
