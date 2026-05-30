<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Order\ListAllOrdersAction;
use App\Actions\Order\UpdateOrderStatusAction;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Order\UpdateOrderStatusRequest;
use App\Http\Resources\OrderListResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(
        private ListAllOrdersAction $listAction,
        private UpdateOrderStatusAction $updateStatusAction,
    ) {}

    /**
     * List all orders (admin view)
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $this->listAction->execute(
            statusFilter: $request->string('status')->toString() ?: null,
            orderNumber: $request->string('order_number')->toString() ?: null,
            perPage: $request->integer('per_page', 20),
        );

        return OrderListResource::collection($orders);
    }

    /**
     * Show any order (admin view)
     */
    public function show(Order $order): OrderResource
    {
        $order->load(['items', 'user', 'statusHistory.changedBy']);

        return new OrderResource($order);
    }

    /**
     * Update order status
     */
    public function updateStatus(
        UpdateOrderStatusRequest $request,
        Order $order,
    ): JsonResponse {
        $newStatus = OrderStatus::from($request->validated('status'));

        $updated = $this->updateStatusAction->execute(
            order: $order,
            newStatus: $newStatus,
            changedBy: $request->user(),
            reason: $request->validated('reason'),
        );

        return response()->json([
            'message' => 'Order status updated',
            'data' => new OrderResource($updated),
        ]);
    }
}
