<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Notifications\OrderConfirmationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class OrderConfirmationNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_uses_mail_channel(): void
    {
        $order = Order::factory()->create();
        $notification = new OrderConfirmationNotification($order);
        
        $channels = $notification->via($order->customer);
        
        $this->assertContains('mail', $channels);
    }

    public function test_notification_creates_mail_message(): void
    {
        $customer = Customer::factory()->create(['name' => 'John Doe']);
        $order = Order::factory()->create([
            'id' => 123,
            'customer_id' => $customer->id,
            'total_amount' => 25.50,
            'status' => 'confirmed'
        ]);
        $order->load('customer');
        
        $notification = new OrderConfirmationNotification($order);
        
        $mailMessage = $notification->toMail($order->customer);
        
        $this->assertInstanceOf(MailMessage::class, $mailMessage);
        $this->assertEquals('Pedido Confirmado - #123', $mailMessage->subject);
        $this->assertStringContainsString('Olá John Doe!', $mailMessage->greeting);
    }

    public function test_notification_includes_order_details(): void
    {
        $customer = Customer::factory()->create(['name' => 'John Doe']);
        $order = Order::factory()->create([
            'id' => 123,
            'customer_id' => $customer->id,
            'total_amount' => 25.50,
            'status' => 'confirmed',
            'order_date' => now()
        ]);
        $order->load('customer');
        
        $notification = new OrderConfirmationNotification($order);
        
        $mailMessage = $notification->toMail($order->customer);
        
        $this->assertStringContainsString('Número: #123', implode('', $mailMessage->introLines));
        $this->assertStringContainsString('Status: Confirmado', implode('', $mailMessage->introLines));
        $this->assertStringContainsString('R$ 25,50', implode('', $mailMessage->introLines));
    }

    public function test_notification_includes_products(): void
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);
        $product = Product::factory()->create(['name' => 'Pastel de Queijo']);
        
        $order->products()->attach($product, [
            'quantity' => 2,
            'unit_price' => 5.50
        ]);
        $order->load(['customer', 'products']);
        
        $notification = new OrderConfirmationNotification($order);
        
        $mailMessage = $notification->toMail($order->customer);
        
        $this->assertStringContainsString('Pastel de Queijo x2', implode('', $mailMessage->introLines));
        $this->assertStringContainsString('R$ 5,50', implode('', $mailMessage->introLines));
    }

    public function test_notification_handles_notes(): void
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'notes' => 'Sem cebola, por favor'
        ]);
        $order->load('customer');
        
        $notification = new OrderConfirmationNotification($order);
        
        $mailMessage = $notification->toMail($order->customer);
        
        $this->assertStringContainsString('Observações:', implode('', $mailMessage->introLines));
        $this->assertStringContainsString('Sem cebola, por favor', implode('', $mailMessage->introLines));
    }

    public function test_notification_to_array(): void
    {
        $order = Order::factory()->create([
            'id' => 123,
            'total_amount' => 25.50,
            'status' => 'confirmed'
        ]);
        
        $notification = new OrderConfirmationNotification($order);
        
        $array = $notification->toArray($order->customer);
        
        $this->assertEquals([
            'order_id' => 123,
            'total_amount' => 25.50,
            'status' => 'confirmed'
        ], $array);
    }

    public function test_status_name_mapping(): void
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'preparing'
        ]);
        $order->load('customer');
        
        $notification = new OrderConfirmationNotification($order);
        
        $mailMessage = $notification->toMail($order->customer);
        
        $this->assertStringContainsString('Status: Em Preparação', implode('', $mailMessage->introLines));
    }
}
