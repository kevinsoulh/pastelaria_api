<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Console\Command;

class EmailSummaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show email system information and available testing commands';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📧 Email System Information');
        $this->info('=' . str_repeat('=', 50));
        
        // System configuration
        $this->newLine();
        $this->info('🔧 Configuration:');
        $this->line('  SMTP Host: ' . config('mail.mailers.smtp.host'));
        $this->line('  SMTP Port: ' . config('mail.mailers.smtp.port'));
        $this->line('  From Address: ' . config('mail.from.address'));
        $this->line('  MailHog Web UI: http://localhost:8025');
        
        // Available data
        $this->newLine();
        $this->info('📊 Available Data:');
        $customerCount = Customer::count();
        $orderCount = Order::count();
        $this->line("  Customers: {$customerCount}");
        $this->line("  Orders: {$orderCount}");
        
        // Available commands
        $this->newLine();
        $this->info('🚀 Available Email Commands:');
        $this->newLine();
        
        $this->comment('  Welcome Emails:');
        $this->line('    php artisan email:test                    # Send to first customer');
        $this->line('    php artisan email:test --customer-id=1    # Send to specific customer');
        
        $this->newLine();
        $this->comment('  Order Confirmation Emails:');
        $this->line('    php artisan email:test-order              # Send for first order');
        $this->line('    php artisan email:test-order --order-id=1 # Send for specific order');
        
        $this->newLine();
        $this->comment('  System Info:');
        $this->line('    php artisan email:info                    # This command');
        
        // Quick test examples
        if ($customerCount > 0 && $orderCount > 0) {
            $this->newLine();
            $this->info('🎯 Quick Test Examples:');
            $sampleCustomer = Customer::first();
            $sampleOrder = Order::with('customer')->first();
            
            $this->line("  # Test welcome email for {$sampleCustomer->name}:");
            $this->line("  php artisan email:test --customer-id={$sampleCustomer->id}");
            
            $this->line("  # Test order confirmation for Order #{$sampleOrder->id} ({$sampleOrder->customer->name}):");
            $this->line("  php artisan email:test-order --order-id={$sampleOrder->id}");
        }
        
        $this->newLine();
        $this->info('📱 Web Interface:');
        $this->line('  Open http://localhost:8025 in your browser to view sent emails');
        
        return 0;
    }
}
