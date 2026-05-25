<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Cart\AddItemToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\CartItemResource;
use App\Http\Resources\CartResource;
use App\Models\CartItem;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends BaseApiController
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function show(Request $request): JsonResponse
    {
        $cart = $request->attributes->get('cart');
        $cart = $this->cartService->getCart($cart);

        return $this->successResponse(new CartResource($cart));
    }

    public function addItem(AddItemToCartRequest $request): JsonResponse
    {
        $cart = $request->attributes->get('cart');

        $item = $this->cartService->addItem(
            $cart,
            $request->integer('product_id'),
            $request->integer('quantity'),
        );

        return $this->createdResponse(new CartItemResource($item), 'Item added to cart');
    }

    public function updateItem(UpdateCartItemRequest $request, CartItem $item): JsonResponse
    {
        $updated = $this->cartService->updateItem($item, $request->integer('quantity'));

        return $this->successResponse(new CartItemResource($updated), 'Cart item updated');
    }

    public function removeItem(Request $request, CartItem $item): JsonResponse
    {
        $this->cartService->removeItem($item);

        return $this->successResponse(null, 'Item removed from cart');
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $request->attributes->get('cart');
        $this->cartService->clearCart($cart);

        return $this->successResponse(null, 'Cart cleared');
    }
}
