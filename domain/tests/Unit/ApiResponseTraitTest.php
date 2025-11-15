<?php

namespace Tests\Unit;

use App\Http\Traits\ApiResponseTrait;
use Tests\TestCase;
use Illuminate\Support\Facades\File;

class ApiResponseTraitTest extends TestCase
{
    public function test_api_response_trait_exists(): void
    {
        $traitExists = trait_exists('App\Http\Traits\ApiResponseTrait');
        $this->assertTrue($traitExists);
    }

    public function test_api_response_trait_has_success_method(): void
    {
        $traitFile = File::get(app_path('Http/Traits/ApiResponseTrait.php'));
        $this->assertStringContainsString('function successResponse', $traitFile);
    }

    public function test_api_response_trait_has_error_method(): void
    {
        $traitFile = File::get(app_path('Http/Traits/ApiResponseTrait.php'));
        $this->assertStringContainsString('function errorResponse', $traitFile);
    }

    public function test_api_response_trait_has_created_response()
    {
        $controller = new class {
            use ApiResponseTrait;
            public function testCreated() {
                return $this->createdResponse(['id' => 1], 'Created successfully');
            }
        };

        $response = $controller->testCreated();
        $this->assertEquals(201, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Created successfully', $data['message']);
        $this->assertEquals(['id' => 1], $data['data']);
    }

    public function test_api_response_trait_has_updated_response()
    {
        $controller = new class {
            use ApiResponseTrait;
            public function testUpdated() {
                return $this->updatedResponse(['id' => 1], 'Updated successfully');
            }
        };

        $response = $controller->testUpdated();
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Updated successfully', $data['message']);
    }

    public function test_api_response_trait_has_deleted_response()
    {
        $controller = new class {
            use ApiResponseTrait;
            public function testDeleted() {
                return $this->deletedResponse('Deleted successfully');
            }
        };

        $response = $controller->testDeleted();
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Deleted successfully', $data['message']);
    }

    public function test_api_response_trait_has_not_found_response()
    {
        $controller = new class {
            use ApiResponseTrait;
            public function testNotFound() {
                return $this->notFoundResponse('Resource not found');
            }
        };

        $response = $controller->testNotFound();
        $this->assertEquals(404, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Resource not found', $data['message']);
    }

    public function test_api_response_trait_has_validation_error_response()
    {
        $controller = new class {
            use ApiResponseTrait;
            public function testValidation() {
                return $this->validationErrorResponse(['field' => 'error'], 'Validation failed');
            }
        };

        $response = $controller->testValidation();
        $this->assertEquals(422, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Validation failed', $data['message']);
        $this->assertArrayHasKey('errors', $data);
    }

    public function test_api_response_trait_error_with_errors_array()
    {
        $controller = new class {
            use ApiResponseTrait;
            public function testErrorWithArray() {
                return $this->errorResponse('Test error', 400, ['field1' => 'error1', 'field2' => 'error2']);
            }
        };

        $response = $controller->testErrorWithArray();
        $this->assertEquals(400, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Test error', $data['message']);
        $this->assertArrayHasKey('errors', $data);
    }

    public function test_api_response_trait_error_without_errors_array()
    {
        $controller = new class {
            use ApiResponseTrait;
            public function testErrorWithoutArray() {
                return $this->errorResponse('Test error', 400);
            }
        };

        $response = $controller->testErrorWithoutArray();
        $this->assertEquals(400, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Test error', $data['message']);
        $this->assertArrayNotHasKey('errors', $data);
    }
}
