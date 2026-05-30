<?php

namespace App\Actions\Order;

use App\Enums\OrderStatus;
use App\Exceptions\Order\InvalidStatusTransitionException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateOrderStatusAction
{
    public function execute(
        Order $order,
        OrderStatus $newStatus,
        User $changedBy,
        ?string $reason = null,
    ): Order {
        // Validate the transition
        if (!$order->status->canTransitionTo($newStatus)) {
            throw new InvalidStatusTransitionException(
                from: $order->status->label(),
                to: $newStatus->label(),
            );
        }

        return DB::transaction(function () use ($order, $newStatus, $changedBy, $reason) {
            $previousStatus = $order->status;

            // Update status + relevant timestamp
            $order->update([
                'status' => $newStatus,
                ...$this->getTimestampForStatus($newStatus),
            ]);

            // Restore stock if cancelling
            if ($newStatus === OrderStatus::CANCELLED) {
                $this->restoreStock($order);
            }

            // Log the transition
            $order->statusHistory()->create([
                'from_status' => $previousStatus,
                'to_status' => $newStatus,
                'changed_by' => $changedBy->id,
                'reason' => $reason ?? 'Status changed by admin',
            ]);

            return $order->fresh(['items', 'statusHistory']);
        });
    }

    private function getTimestampForStatus(OrderStatus $status): array
    {
        return match($status) {
            OrderStatus::PAID => ['paid_at' => now()],
            OrderStatus::SHIPPED => ['shipped_at' => now()],
            OrderStatus::DELIVERED => ['delivered_at' => now()],
            OrderStatus::CANCELLED => ['cancelled_at' => now()],
            default => [],
        };
    }

    private function restoreStock(Order $order): void
    {
        foreach ($order->items as $item) {
            Product::where('id', $item->product_id)
                ->increment('stock', $item->quantity);
        }
    }
}
