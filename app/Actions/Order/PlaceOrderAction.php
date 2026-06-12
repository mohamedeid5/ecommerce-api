<?php

namespace App\Actions\Order;

use App\Enums\OrderStatus;
use App\Events\OrderPlaced;
use App\Exceptions\Order\EmptyCartException;
use App\Exceptions\Order\InsufficientStockException;
use App\Exceptions\Order\ProductUnavailableException;
use App\Exceptions\Order\TooManyOrdersException;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Order\OrderNumberGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PlaceOrderAction
{
     public function __construct(
        private CalculateOrderTotalsAction $calculateTotals,
        private OrderNumberGenerator $numberGenerator,
    ) {}

    public function execute(
        User $user,
        Cart $cart,
        Address $address,
        ?string $customerNotes = null,
    ): Order {

        $this->validateCartNotEmpty($cart);
        $this->validateAddressBelongsToUser($address, $user);

        $recentOrders = $user->orders()
            ->where('created_at', now()->subHour())
            ->count();

        if ($recentOrders > 3) {
            throw new TooManyOrdersException(
                'You can only place 3 orders per hour.'
            );
        }

        // atomic lock
        $lockKey = $user
            ? "user:{$user->id}:place-order"
            : "cart:{$cart->guest_token}:place-order";
        $lock = Cache::lock($lockKey, 10);

        if(!$lock->get()) {
            throw new TooManyOrdersException(
                'You have another order in progress. Please wait a moment and try again.'
            );
        }

        try {
             return DB::transaction(function () use ($user, $cart, $address, $customerNotes) {
                // Lock products and validate stock
                $products = $this->lockAndValidateProducts($cart);

                // Calculate totals
                $totals = $this->calculateTotals->execute($cart);

                // Create the order (placeholder order_number)
                $order = $this->createOrder($user, $address, $totals, $customerNotes);

                // Generate and save the real order_number
                $order->update([
                    'order_number' => $this->numberGenerator->generate($order->id),
                ]);

                // Create order items (snapshot)
                $this->createOrderItems($order, $cart, $products);

                // Decrement stock
                $this->decrementStock($cart, $products);

                // Record initial status history
                $this->recordStatusHistory($order, $user);

                // Clear the cart
                $cart->items()->delete();

                OrderPlaced::dispatch($order->fresh(['items', 'user']));

                return $order->fresh(['items', 'statusHistory']);
            });
        } finally {
            $lock->release();
        }

    }


    /**
     * Ensure cart has items
     */
    private function validateCartNotEmpty(Cart $cart): void
    {
        if ($cart->items->isEmpty()) {
            throw new EmptyCartException();
        }
    }

     /**
     * Ensure address belongs to the user
     */
    private function validateAddressBelongsToUser(Address $address, User $user): void
    {
        if ($address->user_id !== $user->id) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'This address does not belong to you.'
            );
        }
    }

    /**
     * Lock products for update and validate availability + stock
     * Returns indexed array: [product_id => Product]
     */
    private function lockAndValidateProducts(Cart $cart): Collection
    {
        $productIds = $cart->items()->pluck('product_id')->toArray();

        $products = Product::whereIn('id', $productIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach($cart->items as $item) {

            $product = $products->get($item->product_id);

            if (!$product || $product->status->value !== 'active') {
                throw new ProductUnavailableException(
                    productName: $product?->name ?? 'Unknown product',
                );
            }

            if($product->stock < $item->quantity) {
                throw new InsufficientStockException(
                    productName: $product->name,
                    available: $product->stock,
                    requested: $item->quantity,
                );
            }
        }

        return $products;
    }

    /**
     * Create the order with totals and address snapshot
     */
    private function createOrder(
        User $user,
        Address $address,
        array $totals,
        ?string $customerNotes,
    ): Order {

        return Order::create([
            'order_number' => 'PENDING',  // placeholder, will be updated
            'user_id' => $user->id,
            'status' => OrderStatus::PENDING_PAYMENT,
            ...$totals,
            ...$address->toSnapshot(),
            'customer_notes' => $customerNotes,
            'placed_at' => now(),
        ]);
    }

    private function createOrderItems(Order $order, Cart $cart, $products): void
    {
        foreach ($cart->items as $cartItem) {
            $product = $products[$cartItem->product_id];

            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'unit_price' => $cartItem->unit_price,
                'quantity' => $cartItem->quantity,
                'subtotal' => $cartItem->unit_price * $cartItem->quantity,
            ]);
        }
    }

     /**
     * Decrement product stock
     */
    private function decrementStock(Cart $cart, $products): void
    {
        foreach ($cart->items as $cartItem) {
            Product::where('id', $cartItem->product_id)
                ->decrement('stock', $cartItem->quantity);
        }
    }

     /**
     * Record the initial status transition (null → pending_payment)
     */
    private function recordStatusHistory(Order $order, User $user): void
    {
        $order->statusHistory()->create([
            'from_status' => null,
            'to_status' => OrderStatus::PENDING_PAYMENT,
            'changed_by' => $user->id,
            'reason' => 'Order placed by customer',
        ]);
    }
}
