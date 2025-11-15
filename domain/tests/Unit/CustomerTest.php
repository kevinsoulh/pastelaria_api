<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_be_created_with_valid_data(): void
    {
        $customerData = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'phone' => '11999999999',
            'birth_date' => '1990-01-01',
            'address' => 'Rua das Flores, 123',
            'complement' => 'Apt 45',
            'neighborhood' => 'Centro',
            'zip_code' => '01234-567',
        ];

        $customer = Customer::create($customerData);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals('João Silva', $customer->name);
        $this->assertEquals('joao@example.com', $customer->email);
        $this->assertEquals('11999999999', $customer->phone);
        $this->assertDatabaseHas('customers', [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'phone' => '11999999999',
            'address' => 'Rua das Flores, 123',
            'complement' => 'Apt 45',
            'neighborhood' => 'Centro',
            'zip_code' => '01234-567'
        ]);
    }

    public function test_customer_email_must_be_unique(): void
    {
        $customer1 = Customer::factory()->create([
            'email' => 'test@example.com'
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Customer::factory()->create([
            'email' => 'test@example.com'
        ]);
    }

    public function test_customer_has_orders_relationship(): void
    {
        $customer = Customer::factory()->create();
        
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $customer->orders()
        );
    }

    public function test_customer_can_have_multiple_orders(): void
    {
        $customer = Customer::factory()->create();
        $orders = Order::factory()->count(3)->create([
            'customer_id' => $customer->id
        ]);

        $this->assertCount(3, $customer->orders);
        $this->assertEquals($orders->pluck('id')->sort()->values(), $customer->orders->pluck('id')->sort()->values());
    }

    public function test_customer_uses_soft_deletes(): void
    {
        $customer = Customer::factory()->create();
        $customer->delete();

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
        $this->assertNotNull($customer->fresh()->deleted_at);
    }

    public function test_customer_fillable_attributes(): void
    {
        $customer = new Customer();
        
        $expectedFillable = [
            'name',
            'email',
            'phone',
            'birth_date',
            'address',
            'complement',
            'neighborhood',
            'zip_code',
        ];

        $this->assertEquals($expectedFillable, $customer->getFillable());
    }

    public function test_customer_birth_date_is_cast_to_date()
    {
        $customer = Customer::factory()->create([
            'birth_date' => '1990-01-01'
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $customer->birth_date);
        $this->assertEquals('1990-01-01', $customer->birth_date->toDateString());
    }

    public function test_customer_orders_with_products_relationship()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);
        $order->products()->attach($product->id, ['quantity' => 1, 'unit_price' => 10.00]);

        $ordersWithProducts = $customer->ordersWithProducts;

        $this->assertCount(1, $ordersWithProducts);
        $this->assertTrue($ordersWithProducts->first()->relationLoaded('products'));
    }

    public function test_customer_with_complete_relations_scope()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);
        $order->products()->attach($product->id, ['quantity' => 1, 'unit_price' => 10.00]);

        $customerWithRelations = Customer::withCompleteRelations()->find($customer->id);

        $this->assertTrue($customerWithRelations->relationLoaded('orders'));
        $this->assertTrue($customerWithRelations->orders->first()->relationLoaded('products'));
    }
}
