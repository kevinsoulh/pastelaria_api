<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Notifications\WelcomeCustomerNotification;
use Illuminate\Console\Command;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {--customer-id= : ID of the customer to send welcome email to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email functionality by sending a welcome email to a customer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $customerId = $this->option('customer-id');
        
        if ($customerId) {
            $customer = Customer::find($customerId);
            
            if (!$customer) {
                $this->error("Customer with ID {$customerId} not found.");
                return 1;
            }
        } else {
            // Get the first customer
            $customer = Customer::first();
            
            if (!$customer) {
                $this->error('No customers found. Please run seeders first: php artisan db:seed');
                return 1;
            }
        }

        $this->info("Sending welcome email to: {$customer->name} ({$customer->email})");

        try {
            $customer->notify(new WelcomeCustomerNotification($customer));
            $this->info('Email sent successfully!');
            $this->info('Check MailHog at http://localhost:8025 to view the email');
        } catch (\Exception $e) {
            $this->error("Failed to send email: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }
}
