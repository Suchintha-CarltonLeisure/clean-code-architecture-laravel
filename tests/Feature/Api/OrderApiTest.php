<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Order;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_an_order(): void
    {
        $payload = [
            'customer_name' => 'John Doe',
            'items' => [
                [
                    'product_name' => 'Test Product',
                    'product_sku' => 'SKU-TEST-001',
                    'quantity' => 2,
                    'unit_price' => 19.99,
                    'description' => 'A sample item',
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'customer' => [
                        'name',
                        'first_name',
                        'last_name',
                        'initials',
                    ],
                    'items',
                    'pricing' => [
                        'total' => ['amount', 'currency', 'formatted'],
                        'formatted_total',
                        'currency',
                    ],
                    'status' => ['code', 'label'],
                    'summary' => ['item_count', 'total_amount'],
                ],
                'timestamp',
            ])
            ->assertJsonPath('data.customer.name', 'John Doe')
            ->assertJsonPath('data.status.code', 'pending');

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'John Doe',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function it_can_get_list_of_orders(): void
    {
        Order::factory()->count(3)->create();

        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'customer_name',
                    'items',
                    'total_price',
                    'status',
                ],
            ]);
    }

    /** @test */
    public function it_can_show_a_single_order(): void
    {
        $order = Order::factory()->create();

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.customer.name', $order->customer_name)
            ->assertJsonPath('data.status.code', $order->status);
    }

    /** @test */
    public function it_can_update_an_order_items(): void
    {
        $order = Order::factory()->create();

        $payload = [
            'items' => [
                [
                    'product_name' => 'Updated Product',
                    'product_sku' => 'SKU-UPDATED-001',
                    'quantity' => 3,
                    'unit_price' => 15.50,
                    'description' => 'Updated line item',
                ],
            ],
        ];

        $response = $this->putJson("/api/orders/{$order->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'customer_name',
                'items',
                'total_price',
                'status',
            ])
            ->assertJsonPath('id', $order->id)
            ->assertJsonPath('status', $order->status);
    }

    /** @test */
    public function it_can_delete_an_order(): void
    {
        $order = Order::factory()->create();

        $response = $this->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson(['deleted' => true]);

        $this->assertDatabaseMissing('orders', [
            'id' => $order->id,
        ]);
    }
}