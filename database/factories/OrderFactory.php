<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ramsey\Uuid\Uuid;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
      /**
       * The name of the factory's corresponding model.
       *
       * @var class-string<\Illuminate\Database\Eloquent\Model>
       */
      protected $model = Order::class;

      /**
       * Define the model's default state.
       *
       * @return array<string, mixed>
       */
      public function definition(): array
      {
            $items = [];
            $numItems = fake()->numberBetween(1, 4);
            $totalAmount = 0.0;

            for ($i = 0; $i < $numItems; $i++) {
                  $quantity = fake()->numberBetween(1, 5);
                  $unitAmount = fake()->randomFloat(2, 1, 100);
                  $lineAmount = $quantity * $unitAmount;
                  $totalAmount += $lineAmount;

                  $items[] = [
                        'id' => Uuid::uuid4()->toString(),
                        'product_name' => fake()->words(2, true),
                        'product_sku' => strtoupper(fake()->bothify('SKU-#####')),
                        'quantity' => $quantity,
                        'unit_price' => [
                              'amount' => round($unitAmount, 2),
                              'currency' => 'USD',
                              'formatted' => 'USD ' . number_format($unitAmount, 2),
                        ],
                        'total_price' => [
                              'amount' => round($lineAmount, 2),
                              'currency' => 'USD',
                              'formatted' => 'USD ' . number_format($lineAmount, 2),
                        ],
                        'description' => fake()->optional()->sentence(),
                  ];
            }

            return [
                  'customer_name' => fake()->name(),
                  'items' => $items,
                  'total_price' => number_format($totalAmount, 2, '.', ''),
                  'status' => fake()->randomElement(['pending', 'confirmed', 'shipped', 'delivered', 'cancelled']),
            ];
      }
}