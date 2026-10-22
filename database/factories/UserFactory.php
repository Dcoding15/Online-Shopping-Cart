<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_name' => fake()->name(),
            'phone_no' => fake()->unique()->numerify('##########'),
            'user_addr' => fake()->address(),
            'user_type' => fake()->randomElement(['seller', 'admin', 'buyer']),
            'user_cart_quantity' => 0,
            'userpayment_confirmation' => false,
            'user_wallet_amount' => fake()->randomFloat(2, 0, 500),
            'date_of_birth' => fake()->date(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is a buyer.
     */
    public function buyer(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'buyer',
        ]);
    }

    /**
     * Indicate that the user is a seller.
     */
    public function seller(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'seller',
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'admin',
        ]);
    }
}
