<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateProductRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateProductRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorize_returns_true(): void
    {
        $request = new UpdateProductRequest();
        
        $this->assertTrue($request->authorize());
    }

    public function test_validation_rules(): void
    {
        $request = new UpdateProductRequest();
        $rules = $request->rules();
        
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('price', $rules);
        $this->assertArrayHasKey('photo', $rules);
    }

    public function test_valid_partial_data_passes_validation(): void
    {
        $request = new UpdateProductRequest();
        
        $data = [
            'name' => 'Updated Pastel',
            'price' => 6.50
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->passes());
    }

    public function test_empty_data_passes_validation(): void
    {
        $request = new UpdateProductRequest();
        
        $data = [];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->passes());
    }

    public function test_invalid_price_fails_validation(): void
    {
        $request = new UpdateProductRequest();
        
        $data = [
            'price' => -5.50 // negative price
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('price', $validator->errors()->toArray());
    }

    public function test_photo_validation(): void
    {
        $request = new UpdateProductRequest();
        
        $data = [
            'name' => 'Updated Product',
            'photo' => 'valid_photo_url.jpg'
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertTrue($validator->passes());
    }

    public function test_prepares_data_correctly()
    {
        $request = new UpdateProductRequest();
        $request->replace([
            'price' => '  19.99  ',
            'name' => '  Product Name  '
        ]);
        
        $reflection = new \ReflectionMethod($request, 'prepareForValidation');
        $reflection->setAccessible(true);
        $reflection->invoke($request);
        
        // The prepareForValidation method only trims price, not name
        $this->assertEquals(19.99, $request->input('price'));
        $this->assertEquals('  Product Name  ', $request->input('name'));
    }

    public function test_failed_validation_returns_json_response()
    {
        $request = new UpdateProductRequest();
        
        $this->expectException(\Illuminate\Http\Exceptions\HttpResponseException::class);
        
        $validator = $this->app['validator']->make(
            ['price' => 'invalid'],
            $request->rules()
        );
        
        $reflection = new \ReflectionMethod($request, 'failedValidation');
        $reflection->setAccessible(true);
        $reflection->invoke($request, $validator);
    }
}
