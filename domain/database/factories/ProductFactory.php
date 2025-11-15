<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pastelTypes = [
            'Pastel de Carne',
            'Pastel de Queijo',
            'Pastel de Frango',
            'Pastel de Camarão',
            'Pastel de Palmito',
            'Pastel de Pizza',
            'Pastel de Chocolate',
            'Pastel de Banana',
            'Pastel Misto',
            'Pastel Especial'
        ];

        $categories = ['salgado', 'doce', 'especial'];

        return [
            'name' => $this->faker->randomElement($pastelTypes),
            'description' => $this->faker->optional(0.8)->sentence(),
            'price' => $this->faker->randomFloat(2, 5.00, 25.00),
            'category' => $this->faker->randomElement($categories),
            'photo' => $this->faker->optional(0.7)->imageUrl(300, 200, 'food'),
            'is_available' => $this->faker->optional(0.9, true)->boolean(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create a product with specific price
     */
    public function withPrice(float $price): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $price,
        ]);
    }

    /**
     * Create a product without photo
     */
    public function withoutPhoto(): static
    {
        return $this->state(fn (array $attributes) => [
            'photo' => null,
        ]);
    }
}
