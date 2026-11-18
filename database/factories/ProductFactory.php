<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $indianBrands = [
            'Amul',
            'Asian Paints',
            'Bajaj',
            'boAt',
            'Britannia',
            'Dabur',
            'Fabindia',
            'Godrej',
            'Haldiram\'s',
            'Havells',
            'Himalaya',
            'MTR',
            'Mamaearth',
            'Milton',
            'Noise',
            'Parle',
            'Patanjali',
            'Pigeon',
            'Prestige',
            'Royal Enfield',
            'Tata',
            'Titan',
            'Wakefit',
            'Wildcraft',
        ];

        return [
            'product_name' => $this->faker->unique()->words(3, true),
            'user_id' => User::query()
                ->where('user_type', 'seller')
                ->inRandomOrder()
                ->value('user_id') ?? User::factory()->seller(),
            'product_available_quantity' => $this->faker->numberBetween(0, 100),
            'product_price' => $this->faker->randomFloat(2, 10, 1000),
            'product_picture' => null,
            'product_type' => $this->faker->randomElement(array_diff(Product::TYPES, ['Others'])),
            'product_description' => $this->faker->paragraph(),
            'product_category' => $this->faker->randomElement(array_diff(Product::CATEGORIES, ['Others'])),
            'product_meta_data' => [
                'brand' => $this->faker->randomElement($indianBrands),
                'color' => $this->faker->safeColorName(),
                'size' => $this->faker->randomElement(['S', 'M', 'L', 'XL', 'One Size']),
                'featured' => $this->faker->boolean(),
            ],
        ];
    }
}
