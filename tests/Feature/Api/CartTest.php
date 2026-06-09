<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Override;
use Tests\TestCase;
use Tests\Traits\CreatesRoles;

class CartTest extends TestCase
{
    use CreatesRoles, RefreshDatabase;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_guest_can_add_item_to_cart(): void
    {
        Category::factory()->create(['id' => 1]);
        $product = Product::factory()->create([
            'stock' => 10,
            'price' => 100
        ]);

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 5
        ]);

        $response->assertStatus(201);
        $response->assertHeader('X-Cart-Token');

        $this->assertDatabaseHas('cart_items',[
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_price' => 100
        ]);
    }

    public function test_cart_persists_across_requests_with_token(): void
    {
        Category::factory()->create(['id' => 1]);
        $product = Product::factory()->create([
            'stock' => 10,
            'price' => 100
        ]);

        $first = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $cartToken = $first->headers->get('X-Cart-Token');

        $second = $this->withHeader('X-Cart-Token', $cartToken)->getJson('api/v1/cart');
        $second->assertStatus(200);
        $second->assertJsonPath('data.items.0.product.id', $product->id);
    }

    public function test_cannot_add_more_than_available_stock(): void
    {
        Category::factory()->create(['id' => 1]);
        $product = Product::factory()->create([
            'stock' => 10,
            'price' => 100
        ]);

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 12,
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);

    }
}
