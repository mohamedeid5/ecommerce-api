<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'full_name' => fake()->name(),
            'phone' => fake()->numerify('01#########'),
            'street' => fake()->streetAddress(),
            'building' => fake()->optional()->buildingNumber(),
            'apartment' => fake()->optional()->numberBetween(1, 50),
            'city' => fake()->city(),
            'governorate' => fake()->randomElement([
                'Cairo',
                'Giza',
                'Alexandria',
                'Dakahlia',
                'Sharqia',
                'Qalyubia',
            ]),
            'postal_code' => fake()->optional()->numerify('#####'),
            'notes' => fake()->optional()->sentence(),
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(['is_default' => true]);
    }

    public function notDefault(): static
    {
        return $this->state(['is_default' => false]);
    }
}
