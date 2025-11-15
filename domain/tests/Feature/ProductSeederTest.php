<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_seeder_creates_products(): void
    {
        $this->seed(ProductSeeder::class);

        $this->assertTrue(Product::count() > 0);
        
        $this->assertDatabaseHas('products', [
            'name' => 'Pastel de Carne',
            'category' => 'salgado'
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Pastel de Queijo',
            'category' => 'salgado'
        ]);
    }

    public function test_product_seeder_creates_products_with_required_fields(): void
    {
        $this->seed(ProductSeeder::class);

        $products = Product::all();
        
        foreach ($products as $product) {
            $this->assertNotNull($product->name);
            $this->assertNotNull($product->price);
            $this->assertNotNull($product->category);
            $this->assertNotNull($product->is_available);
            $this->assertGreaterThan(0, $product->price);
        }
    }

    public function test_product_seeder_creates_different_categories(): void
    {
        $this->seed(ProductSeeder::class);

        $categories = Product::distinct('category')->pluck('category')->toArray();
        
        $this->assertContains('salgado', $categories);
        $this->assertContains('doce', $categories);
        $this->assertContains('especial', $categories);
    }

    public function test_product_seeder_creates_available_and_unavailable_products(): void
    {
        $this->seed(ProductSeeder::class);

        $availableCount = Product::where('is_available', true)->count();
        $unavailableCount = Product::where('is_available', false)->count();
        
        $this->assertGreaterThan(0, $availableCount);
        $this->assertTrue($availableCount + $unavailableCount === Product::count());
    }

    public function test_product_prices_are_reasonable(): void
    {
        $this->seed(ProductSeeder::class);

        $products = Product::all();
        
        foreach ($products as $product) {
            $this->assertGreaterThanOrEqual(1.00, $product->price);
            $this->assertLessThanOrEqual(50.00, $product->price);
        }
    }

    public function test_product_names_are_not_empty(): void
    {
        $this->seed(ProductSeeder::class);

        $products = Product::all();
        
        foreach ($products as $product) {
            $this->assertNotEmpty(trim($product->name));
            $this->assertGreaterThan(3, strlen($product->name));
        }
    }
}