<?php

namespace App\Actions\Order;

use App\Enums\OrderStatus;
use App\Exceptions\Order\OrderNotCancellableException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CancelOrderAction
{
    public function execute(
        Order $order,
        User $cancelledBy,
        ?string $reason = null,
    ): Order {

        $this->validateCanCancel($order);

        return DB::transaction(function () use ($order, $cancelledBy, $reason) {
            $previousStatus = $order->status;

            // Restore the stock
            $this->restoreStock($order);

            // Update the order
            $order->update([
                'status' => OrderStatus::CANCELLED,
                'cancelled_at' => now(),
            ]);

            // Log the status change
            $order->statusHistory()->create([
                'from_status' => $previousStatus,
                'to_status' => OrderStatus::CANCELLED,
                'changed_by' => $cancelledBy->id,
                'reason' => $reason ?? 'Order cancelled',
            ]);

            return $order->fresh(['items', 'statusHistory']);
        });
    }

    /**
     * Ensure the order can be cancelled
     */
    private function validateCanCancel(Order $order): void
    {
        if (!$order->status->isCancellable()) {
            throw new OrderNotCancellableException(
                currentStatus: $order->status->label(),
            );
        }
    }

    /**
     * Return stock to inventory
     */
    private function restoreStock(Order $order): void
    {
        foreach ($order->items as $item) {
            Product::where('id', $item->product_id)
                ->increment('stock', $item->quantity);
        }
    }
}
