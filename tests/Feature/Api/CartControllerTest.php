<?php

namespace Tests\Feature\Api;

use App\Enums\ProductStatus;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_update_an_item_from_another_cart(): void
    {
        [$ownerCart, $otherCart, $item] = $this->createCartsWithItem();

        $this->withHeader('X-Cart-Token', $otherCart->guest_token)
            ->patchJson("/api/v1/cart/items/{$item->id}", ['quantity' => 2])
            ->assertForbidden();

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'cart_id' => $ownerCart->id,
            'quantity' => 1,
        ]);
    }

    public function test_guest_cannot_delete_an_item_from_another_cart(): void
    {
        [$ownerCart, $otherCart, $item] = $this->createCartsWithItem();

        $this->withHeader('X-Cart-Token', $otherCart->guest_token)
            ->deleteJson("/api/v1/cart/items/{$item->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'cart_id' => $ownerCart->id,
        ]);
    }

    public function test_guest_can_update_an_item_from_the_current_cart(): void
    {
        [$cart, , $item] = $this->createCartsWithItem();

        $this->withHeader('X-Cart-Token', $cart->guest_token)
            ->patchJson("/api/v1/cart/items/{$item->id}", ['quantity' => 2])
            ->assertOk();

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'cart_id' => $cart->id,
            'quantity' => 2,
        ]);
    }

    public function test_authenticated_user_gets_the_same_cart_on_each_request(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/cart')
            ->assertOk()
            ->assertHeaderMissing('X-Cart-Token');

        $this->withToken($token)
            ->getJson('/api/v1/cart')
            ->assertOk()
            ->assertHeaderMissing('X-Cart-Token');

        $this->assertDatabaseCount('carts', 1);
        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'guest_token' => null,
        ]);
    }

    private function createCartsWithItem(): array
    {
        $category = Category::create([
            'name' => 'Category',
            'slug' => 'category',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Product',
            'slug' => 'product',
            'price' => 100,
            'stock' => 10,
            'sku' => 'SKU-1',
            'status' => ProductStatus::ACTIVE,
        ]);

        $ownerCart = Cart::create([
            'guest_token' => 'owner-cart-token',
            'expires_at' => now()->addDay(),
        ]);

        $otherCart = Cart::create([
            'guest_token' => 'other-cart-token',
            'expires_at' => now()->addDay(),
        ]);

        $item = $ownerCart->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        return [$ownerCart, $otherCart, $item];
    }
}
