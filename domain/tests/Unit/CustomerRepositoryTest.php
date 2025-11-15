<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use App\Repositories\CustomerRepository;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected CustomerRepositoryInterface $customerRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerRepository = app(CustomerRepositoryInterface::class);
    }

    public function test_can_create_customer_through_repository(): void
    {
        $customerData = [
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'phone' => '11999999999',
            'address' => 'Test Address',
            'neighborhood' => 'Test Neighborhood',
            'zip_code' => '12345-678'
        ];

        $customer = $this->customerRepository->create($customerData);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertDatabaseHas('customers', $customerData);
        $this->assertEquals('Test Customer', $customer->name);
    }

    public function test_can_find_customers_by_email(): void
    {
        Customer::factory()->create(['email' => 'john@gmail.com']);
        Customer::factory()->create(['email' => 'jane@gmail.com']);
        Customer::factory()->create(['email' => 'bob@yahoo.com']);

        $customers = $this->customerRepository->findByEmail('gmail');

        $this->assertCount(2, $customers);
        foreach ($customers as $customer) {
            /** @var \App\Models\Customer $customer */
            $this->assertStringContainsString('gmail', $customer->email);
        }
    }

    public function test_can_find_customers_by_name(): void
    {
        Customer::factory()->create(['name' => 'João Silva']);
        Customer::factory()->create(['name' => 'João Santos']);
        Customer::factory()->create(['name' => 'Maria João']);

        $customers = $this->customerRepository->findByName('João');

        $this->assertCount(3, $customers);
        foreach ($customers as $customer) {
            /** @var \App\Models\Customer $customer */
            $this->assertStringContainsString('João', $customer->name);
        }
    }

    public function test_can_check_email_uniqueness(): void
    {
        $customer = Customer::factory()->create(['email' => 'existing@example.com']);

        $this->assertFalse($this->customerRepository->isEmailUnique('existing@example.com'));
        $this->assertTrue($this->customerRepository->isEmailUnique('new@example.com'));
        $this->assertTrue($this->customerRepository->isEmailUnique('existing@example.com', $customer->id));
    }

    public function test_can_get_customers_with_orders(): void
    {
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();
        
        Order::factory()->count(2)->create(['customer_id' => $customer1->id]);
        Order::factory()->count(1)->create(['customer_id' => $customer2->id]);

        $customers = $this->customerRepository->getWithOrders();

        $this->assertCount(2, $customers->items());
        
        foreach ($customers->items() as $customer) {
            $this->assertTrue($customer->relationLoaded('orders'));
        }
    }

    public function test_can_update_customer_through_repository(): void
    {
        $customer = Customer::factory()->create(['name' => 'Original Name']);
        
        $updatedCustomer = $this->customerRepository->update($customer, ['name' => 'Updated Name']);

        $this->assertEquals('Updated Name', $updatedCustomer->name);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Name'
        ]);
    }

    public function test_can_delete_customer_through_repository(): void
    {
        $customer = Customer::factory()->create();
        
        $result = $this->customerRepository->delete($customer);

        $this->assertTrue($result);
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_repository_implements_correct_interface(): void
    {
        $this->assertInstanceOf(CustomerRepositoryInterface::class, $this->customerRepository);
        $this->assertInstanceOf(CustomerRepository::class, $this->customerRepository);
    }
}
