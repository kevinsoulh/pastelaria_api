<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Notifications\OrderConfirmationNotification;
use Illuminate\Console\Command;

class TestOrderEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test-order {--order-id= : ID of the order to send confirmation email for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test order confirmation email functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderId = $this->option('order-id');
        
        if ($orderId) {
            $order = Order::with(['customer', 'products'])->find($orderId);
            
            if (!$order) {
                $this->error("Order with ID {$orderId} not found.");
                return 1;
            }
        } else {
            // Get the first order
            $order = Order::with(['customer', 'products'])->first();
            
            if (!$order) {
                $this->error('No orders found. Please run seeders first: php artisan db:seed');
                return 1;
            }
        }

        $this->info("Sending order confirmation email for Order #{$order->id}");
        $this->info("Customer: {$order->customer->name} ({$order->customer->email})");
        $this->info("Total: R$ " . number_format((float) $order->total_amount, 2, ',', '.'));
        $this->info("Products: " . $order->products->count());

        try {
            $order->customer->notify(new OrderConfirmationNotification($order));
            $this->info('Order confirmation email sent successfully!');
            $this->info('Check MailHog at http://localhost:8025 to view the email');
        } catch (\Exception $e) {
            $this->error("Failed to send email: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }
}
