<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateOrderRequest;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateOrderRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorize_returns_true(): void
    {
        $request = new UpdateOrderRequest();
        
        $this->assertTrue($request->authorize());
    }

    public function test_validation_rules(): void
    {
        $request = new UpdateOrderRequest();
        $rules = $request->rules();
        
        $this->assertArrayHasKey('status', $rules);
        $this->assertArrayHasKey('notes', $rules);
    }

    public function test_valid_partial_data_passes_validation(): void
    {
        $request = new UpdateOrderRequest();
        
        $data = [
            'status' => 'confirmed',
            'notes' => 'Updated notes'
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->passes());
    }

    public function test_empty_data_passes_validation(): void
    {
        $request = new UpdateOrderRequest();
        
        $data = [];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->passes());
    }

    public function test_notes_validation(): void
    {
        $request = new UpdateOrderRequest();
        
        $data = [
            'notes' => 'Valid notes',
            'status' => 'confirmed'
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->passes());
    }

    public function test_invalid_status_fails_validation(): void
    {
        $request = new UpdateOrderRequest();
        
        $data = [
            'status' => 'invalid_status'
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    public function test_valid_customer_id_passes_validation(): void
    {
        $customer = Customer::factory()->create();
        $request = new UpdateOrderRequest();
        
        $data = [
            'customer_id' => $customer->id,
            'status' => 'confirmed'
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->passes());
    }
}
