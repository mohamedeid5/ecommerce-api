<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $price = fake()->randomFloat(2, 5, 1000);

        return [
            'category_id' => Category::factory(),
            'name'        => ucfirst($name),
            'slug'        => Str::slug($name),
            'description' => fake()->optional()->paragraph(),
            'price'       => $price,
            'sale_price'  => null,
            'stock'       => fake()->numberBetween(1, 500),
            'sku'         => fake()->unique()->bothify('SKU-####-????'),
            'status'      => ProductStatus::ACTIVE,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => ProductStatus::ACTIVE]);
    }

    public function inactive(): static
    {
        return $this->state(['status' => ProductStatus::INACTIVE]);
    }

    public function draft(): static
    {
        return $this->state(['status' => ProductStatus::DRAFT]);
    }

    public function archived(): static
    {
        return $this->state(['status' => ProductStatus::ARCHIVED]);
    }

    public function outOfStock(): static
    {
        return $this->state(['stock' => 0]);
    }
}
