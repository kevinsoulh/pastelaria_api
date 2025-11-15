<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing customers and products
        $customers = Customer::all();
        $products = Product::all();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        // Create sample orders
        for ($i = 1; $i <= 25; $i++) {
            $customer = $customers->random();
            $orderProducts = $products->random(rand(1, 4)); // 1 to 4 products per order
            
            $totalAmount = 0;
            $productData = [];
            
            foreach ($orderProducts as $product) {
                $quantity = rand(1, 3);
                $unitPrice = $product->price;
                $totalAmount += $quantity * $unitPrice;
                
                $productData[$product->id] = [
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice
                ];
            }
            
            $statuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'];
            $status = $statuses[array_rand($statuses)];
            
            // Create order
            $order = Order::create([
                'customer_id' => $customer->id,
                'total_amount' => $totalAmount,
                'status' => $status,
                'notes' => rand(1, 3) == 1 ? 'Observação do pedido #' . $i : null,
                'order_date' => now()->subDays(rand(0, 30))->subHours(rand(0, 23))
            ]);
            
            // Attach products to order
            $order->products()->attach($productData);
        }
    }
}
