<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_can_list_customers(): void
    {
        Customer::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/customers');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'phone',
                            'birth_date',
                            'address',
                            'complement',
                            'neighborhood',
                            'zip_code',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'meta' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total'
                    ]
                ])
                ->assertJsonPath('success', true);

        $this->assertCount(5, $response->json('data'));
    }

    public function test_can_create_customer(): void
    {
        $customerData = [
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'phone' => '11987654321',
            'birth_date' => '1985-06-15',
            'address' => 'Rua das Palmeiras, 456',
            'complement' => 'Casa 2',
            'neighborhood' => 'Jardim América',
            'zip_code' => '01234-567',
        ];

        $response = $this->postJson('/api/v1/customers', $customerData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'birth_date',
                        'address',
                        'complement',
                        'neighborhood',
                        'zip_code',
                        'created_at',
                        'updated_at'
                    ]
                ])
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.name', 'Maria Silva')
                ->assertJsonPath('data.email', 'maria@example.com');

        $this->assertDatabaseHas('customers', [
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'phone' => '11987654321',
            'neighborhood' => 'Jardim América',
            'zip_code' => '01234-567'
        ]);
        
        $customer = Customer::where('email', 'maria@example.com')->first();
        $this->assertNotNull($customer);
        $this->assertNotNull($customer->created_at);
        $this->assertNotNull($customer->updated_at);
        $this->assertNull($customer->deleted_at);
        
        $this->assertDatabaseCount('customers', 1);
    }

    public function test_create_customer_validation_fails(): void
    {
        $invalidData = [
            'name' => '',
            'email' => 'invalid-email',
            'phone' => '',
            'address' => '',
            'neighborhood' => '',
            'zip_code' => ''
        ];

        $response = $this->postJson('/api/v1/customers', $invalidData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email', 'phone', 'address', 'neighborhood', 'zip_code']);
    }

    public function test_create_customer_unique_email_validation(): void
    {
        Customer::factory()->create(['email' => 'existing@example.com']);

        $customerData = [
            'name' => 'João Santos',
            'email' => 'existing@example.com',
            'phone' => '11999887766',
            'address' => 'Rua Nova, 123',
            'neighborhood' => 'Centro',
            'zip_code' => '12345-678'
        ];

        $response = $this->postJson('/api/v1/customers', $customerData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    public function test_can_show_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->getJson("/api/v1/customers/{$customer->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'birth_date',
                        'address',
                        'complement',
                        'neighborhood',
                        'zip_code',
                        'created_at',
                        'updated_at',
                        'orders'
                    ]
                ])
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.id', $customer->id);
    }

    public function test_can_update_customer(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com'
        ]);
        
        $originalUpdatedAt = $customer->updated_at;
        
        sleep(1);
        
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ];

        $response = $this->putJson("/api/v1/customers/{$customer->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.name', 'Updated Name')
                ->assertJsonPath('data.email', 'updated@example.com');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
        
        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
            'name' => 'Original Name',
            'email' => 'original@example.com'
        ]);
        
        $updatedCustomer = Customer::find($customer->id);
        $this->assertTrue($updatedCustomer->updated_at->gt($originalUpdatedAt));
        
        $this->assertDatabaseCount('customers', 1);
    }

    public function test_can_delete_customer(): void
    {
        $customer = Customer::factory()->create();
        $customerId = $customer->id;

        $response = $this->deleteJson("/api/v1/customers/{$customerId}");

        $response->assertStatus(200)
                ->assertJsonPath('success', true);

        $this->assertSoftDeleted('customers', ['id' => $customerId]);
        
        $this->assertDatabaseHas('customers', [
            'id' => $customerId,
        ]);
        
        $deletedCustomer = Customer::withTrashed()->find($customerId);
        $this->assertNotNull($deletedCustomer->deleted_at);
        
        $this->assertNull(Customer::find($customerId));
        
        $this->assertNotNull(Customer::withTrashed()->find($customerId));
    }

    public function test_can_filter_customers_by_name(): void
    {
        Customer::factory()->create(['name' => 'João Silva']);
        Customer::factory()->create(['name' => 'Maria Santos']);
        Customer::factory()->create(['name' => 'Pedro João']);

        $response = $this->getJson('/api/v1/customers?name=João');

        $response->assertStatus(200);
        
        $customers = $response->json('data');
        $this->assertCount(2, $customers);
        
        foreach ($customers as $customer) {
            $this->assertStringContainsString('João', $customer['name']);
        }
    }

    public function test_can_filter_customers_by_email(): void
    {
        Customer::factory()->create(['email' => 'test@gmail.com']);
        Customer::factory()->create(['email' => 'user@yahoo.com']);
        Customer::factory()->create(['email' => 'admin@gmail.com']);

        $response = $this->getJson('/api/v1/customers?email=gmail');

        $response->assertStatus(200);
        
        $customers = $response->json('data');
        $this->assertCount(2, $customers);
        
        foreach ($customers as $customer) {
            $this->assertStringContainsString('gmail', $customer['email']);
        }
    }

    public function test_shows_customer_with_orders(): void
    {
        $customer = Customer::factory()->create();
        $orders = Order::factory()->count(2)->create(['customer_id' => $customer->id]);

        $response = $this->getJson("/api/v1/customers/{$customer->id}");

        $response->assertStatus(200)
                ->assertJsonPath('success', true);

        $customerData = $response->json('data');
        $this->assertArrayHasKey('orders', $customerData);
        $this->assertCount(2, $customerData['orders']);
    }
}
