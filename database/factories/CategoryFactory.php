<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'parent_id'   => null,
            'name'        => ucfirst($name),
            'slug'        => Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'image'       => null,
            'is_active'   => true,
        ];
    }

    public function withParent(): static
    {
        return $this->state(['parent_id' => Category::factory()]);
    }

    public function active(): static
    {
        return $this->state(['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function child(): static
    {
        return $this->withParent();
    }
}
