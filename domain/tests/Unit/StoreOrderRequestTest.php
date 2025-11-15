<?php

namespace Tests\Unit;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreOrderRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorize_returns_true(): void
    {
        $request = new StoreOrderRequest();
        
        $this->assertTrue($request->authorize());
    }

    public function test_validation_rules(): void
    {
        $request = new StoreOrderRequest();
        $rules = $request->rules();
        
        $this->assertArrayHasKey('customer_id', $rules);
        $this->assertArrayHasKey('notes', $rules);
        $this->assertArrayHasKey('products', $rules);
        $this->assertArrayHasKey('products.*.product_id', $rules);
        $this->assertArrayHasKey('products.*.quantity', $rules);
    }

    public function test_valid_data_passes_validation(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        
        $request = new StoreOrderRequest();
        
        $data = [
            'customer_id' => $customer->id,
            'status' => 'pending',
            'notes' => 'Test order',
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 10.50
                ]
            ]
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->passes());
    }

    public function test_missing_required_fields_fails_validation(): void
    {
        $request = new StoreOrderRequest();
        
        $data = [];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('customer_id', $validator->errors()->toArray());
    }

    public function test_invalid_customer_id_fails_validation(): void
    {
        $request = new StoreOrderRequest();
        
        $data = [
            'customer_id' => 99999, // non-existent customer
            'status' => 'pending'
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('customer_id', $validator->errors()->toArray());
    }

    public function test_products_array_validation(): void
    {
        $request = new StoreOrderRequest();
        $customer = Customer::factory()->create();
        
        $data = [
            'customer_id' => $customer->id,
            'products' => [] // Empty products array should fail
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('products', $validator->errors()->toArray());
    }

    public function test_invalid_product_data_fails_validation(): void
    {
        $customer = Customer::factory()->create();
        $request = new StoreOrderRequest();
        
        $data = [
            'customer_id' => $customer->id,
            'status' => 'pending',
            'products' => [
                [
                    'product_id' => 99999, // non-existent product
                    'quantity' => 0, // invalid quantity
                    'unit_price' => -5 // invalid price
                ]
            ]
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertFalse($validator->passes());
    }
}
