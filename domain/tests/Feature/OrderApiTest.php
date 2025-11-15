<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_orders(): void
    {
        Order::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/orders');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'customer_id',
                            'total_amount',
                            'status',
                            'order_date'
                        ]
                    ],
                    'meta' => [
                        'current_page',
                        'total',
                        'per_page'
                    ]
                ])
                ->assertJsonPath('success', true);
    }

    public function test_can_create_order(): void
    {
        $customer = Customer::factory()->create();
        $products = Product::factory()->count(2)->create();
        
        $orderData = [
            'customer_id' => $customer->id,
            'status' => 'pending',
            'notes' => 'Test order',
            'products' => [
                [
                    'product_id' => $products[0]->id,
                    'quantity' => 2,
                    'unit_price' => $products[0]->price
                ],
                [
                    'product_id' => $products[1]->id,
                    'quantity' => 1,
                    'unit_price' => $products[1]->price
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/orders', $orderData);

        $response->assertStatus(201)
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.customer_id', $customer->id)
                ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'status' => 'pending'
        ]);
    }

    public function test_create_order_validation_fails(): void
    {
        $response = $this->postJson('/api/v1/orders', []);

        $response->assertStatus(422)
                ->assertJsonPath('success', false)
                ->assertJsonValidationErrors(['customer_id']);
    }

    public function test_can_show_order(): void
    {
        $order = Order::factory()->create();

        $response = $this->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.id', $order->id)
                ->assertJsonPath('data.customer_id', $order->customer_id);
    }

    public function test_can_update_order(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);
        
        $updateData = [
            'status' => 'confirmed',
            'notes' => 'Updated notes'
        ];

        $response = $this->putJson("/api/v1/orders/{$order->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.status', 'confirmed');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed'
        ]);
    }

    public function test_can_delete_order(): void
    {
        $order = Order::factory()->pending()->create();

        $response = $this->deleteJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('message', 'Order deleted successfully');

        $this->assertSoftDeleted('orders', ['id' => $order->id]);
    }

    public function test_can_filter_orders_by_customer(): void
    {
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();
        
        Order::factory()->create(['customer_id' => $customer1->id]);
        Order::factory()->create(['customer_id' => $customer2->id]);
        Order::factory()->create(['customer_id' => $customer1->id]);

        $response = $this->getJson("/api/v1/orders?customer_id={$customer1->id}");

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_orders_by_status(): void
    {
        Order::factory()->create(['status' => 'pending']);
        Order::factory()->create(['status' => 'confirmed']);
        Order::factory()->create(['status' => 'pending']);

        $response = $this->getJson('/api/v1/orders?status=pending');

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonCount(2, 'data');
    }

    public function test_shows_order_with_products_and_customer(): void
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create();
        $order->products()->attach($product, [
            'quantity' => 2,
            'unit_price' => $product->price
        ]);

        $response = $this->getJson("/api/v1/orders/{$order->id}?include=customer,products");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'customer',
                        'products'
                    ]
                ])
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.id', $order->id);
    }
}
