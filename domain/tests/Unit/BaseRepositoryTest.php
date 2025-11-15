<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\TestBaseRepository;
use Tests\TestCase;

class BaseRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected TestBaseRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TestBaseRepository();
    }

    public function test_all_method_returns_all_records()
    {
        Product::factory()->count(3)->create();

        $results = $this->repository->all();

        $this->assertCount(3, $results);
    }

    public function test_all_method_with_relationships()
    {
        Product::factory()->count(2)->create();

        $results = $this->repository->all(['orders']);

        $this->assertCount(2, $results);
        // Since we're loading with 'orders', the relationship should be loaded (even if empty)
        $this->assertTrue($results->first()->relationLoaded('orders'));
    }

    public function test_paginate_method()
    {
        Product::factory()->count(20)->create();

        $results = $this->repository->paginate(5);

        $this->assertEquals(5, $results->count());
        $this->assertEquals(20, $results->total());
        $this->assertEquals(4, $results->lastPage());
    }

    public function test_paginate_method_with_relationships()
    {
        Product::factory()->count(10)->create();

        $results = $this->repository->paginate(5, ['orders']);

        $this->assertEquals(5, $results->count());
        $this->assertTrue($results->first()->relationLoaded('orders'));
    }

    public function test_find_method_returns_model()
    {
        $product = Product::factory()->create();

        $result = $this->repository->find($product->id);

        $this->assertNotNull($result);
        $this->assertEquals($product->id, $result->id);
    }

    public function test_find_method_returns_null_for_nonexistent()
    {
        $result = $this->repository->find(99999);

        $this->assertNull($result);
    }

    public function test_find_method_with_relationships()
    {
        $product = Product::factory()->create();

        $result = $this->repository->find($product->id, ['orders']);

        $this->assertNotNull($result);
        $this->assertTrue($result->relationLoaded('orders'));
    }

    public function test_find_or_fail_method_returns_model()
    {
        $product = Product::factory()->create();

        $result = $this->repository->findOrFail($product->id);

        $this->assertNotNull($result);
        $this->assertEquals($product->id, $result->id);
    }

    public function test_find_or_fail_method_throws_exception()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repository->findOrFail(99999);
    }

    public function test_find_or_fail_method_with_relationships()
    {
        $product = Product::factory()->create();

        $result = $this->repository->findOrFail($product->id, ['orders']);

        $this->assertTrue($result->relationLoaded('orders'));
    }

    public function test_create_method()
    {
        $data = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 10.99,
            'category' => 'test'
        ];

        $result = $this->repository->create($data);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertEquals('Test Product', $result->name);
        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
    }

    public function test_update_method()
    {
        $product = Product::factory()->create(['name' => 'Original Name']);
        $updateData = ['name' => 'Updated Name'];

        $result = $this->repository->update($product, $updateData);

        $this->assertEquals('Updated Name', $result->name);
        $this->assertDatabaseHas('products', ['name' => 'Updated Name']);
    }

    public function test_delete_method()
    {
        $product = Product::factory()->create();

        $result = $this->repository->delete($product);

        $this->assertTrue($result);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_find_by_method()
    {
        Product::factory()->create(['category' => 'electronics', 'name' => 'Phone']);
        Product::factory()->create(['category' => 'electronics', 'name' => 'Laptop']);
        Product::factory()->create(['category' => 'books', 'name' => 'Novel']);

        $results = $this->repository->findBy(['category' => 'electronics']);

        $this->assertCount(2, $results);
        $results->each(function ($product) {
            $this->assertEquals('electronics', $product->category);
        });
    }

    public function test_find_by_method_with_relationships()
    {
        Product::factory()->create(['category' => 'electronics']);

        $results = $this->repository->findBy(['category' => 'electronics'], ['orders']);

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->relationLoaded('orders'));
    }

    public function test_find_one_by_method()
    {
        Product::factory()->create(['category' => 'electronics', 'name' => 'Phone']);
        Product::factory()->create(['category' => 'books', 'name' => 'Novel']);

        $result = $this->repository->findOneBy(['category' => 'electronics']);

        $this->assertNotNull($result);
        $this->assertEquals('electronics', $result->category);
    }

    public function test_find_one_by_method_returns_null()
    {
        $result = $this->repository->findOneBy(['category' => 'nonexistent']);

        $this->assertNull($result);
    }

    public function test_find_one_by_method_with_relationships()
    {
        Product::factory()->create(['category' => 'electronics']);

        $result = $this->repository->findOneBy(['category' => 'electronics'], ['orders']);

        $this->assertNotNull($result);
        $this->assertTrue($result->relationLoaded('orders'));
    }
}