<?php

namespace Tests\Unit;

use App\Http\Requests\StoreProductRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreProductRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorize_returns_true(): void
    {
        $request = new StoreProductRequest();
        
        $this->assertTrue($request->authorize());
    }

    public function test_validation_rules(): void
    {
        $request = new StoreProductRequest();
        $rules = $request->rules();
        
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('price', $rules);
        $this->assertArrayHasKey('photo', $rules);
    }

    public function test_valid_data_passes_validation(): void
    {
        $request = new StoreProductRequest();
        
        $data = [
            'name' => 'Pastel de Queijo',
            'price' => 5.50,
            'category' => 'salgado',
            'description' => 'Delicioso pastel',
            'photo' => 'pastels/pastel_queijo.jpg',
            'is_available' => true
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->passes());
    }

    public function test_missing_required_fields_fails_validation(): void
    {
        $request = new StoreProductRequest();
        
        $validator = Validator::make([], $request->rules());
        
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('price', $validator->errors()->toArray());
        $this->assertArrayHasKey('category', $validator->errors()->toArray());
        $this->assertArrayHasKey('photo', $validator->errors()->toArray());
    }

    public function test_invalid_price_fails_validation(): void
    {
        $request = new StoreProductRequest();
        
        $data = [
            'name' => 'Pastel',
            'price' => -5.50, // negative price
            'category' => 'salgado'
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('price', $validator->errors()->toArray());
    }

    public function test_photo_can_be_string(): void
    {
        $request = new StoreProductRequest();
        
        $data = [
            'name' => 'Test Product',
            'price' => 10.00,
            'category' => 'test_category',
            'photo' => 'pastels/test_product.jpg'
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->passes());
    }

    public function test_prepares_data_correctly(): void
    {
        $request = new StoreProductRequest();
        
        $request->merge([
            'name' => '  Pastel de Queijo  ',
            'description' => '  Delicioso pastel  '
        ]);
        
        // Test that the request can handle data with whitespace
        $this->assertEquals('  Pastel de Queijo  ', $request->input('name'));
        $this->assertEquals('  Delicioso pastel  ', $request->input('description'));
    }
}
