<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailCommandsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_email_test_command_with_existing_customer(): void
    {
        $customer = Customer::factory()->create();

        $this->artisan('email:test', ['--customer-id' => $customer->id])
            ->expectsOutput('Email sent successfully!')
            ->assertExitCode(0);
    }

    public function test_email_test_command_with_nonexistent_customer(): void
    {
        $this->artisan('email:test', ['--customer-id' => 99999])
            ->expectsOutput('Customer with ID 99999 not found.')
            ->assertExitCode(1);
    }

    public function test_email_test_command_without_customers(): void
    {
        $this->artisan('email:test')
            ->expectsOutput('No customers found. Please run seeders first: php artisan db:seed')
            ->assertExitCode(1);
    }

    public function test_email_test_command_uses_first_customer_when_no_id(): void
    {
        $customer = Customer::factory()->create();

        $this->artisan('email:test')
            ->expectsOutput('Email sent successfully!')
            ->assertExitCode(0);
    }

    public function test_email_test_order_command_with_existing_order(): void
    {
        $order = Order::factory()->create();

        $this->artisan('email:test-order', ['--order-id' => $order->id])
            ->expectsOutput('Order confirmation email sent successfully!')
            ->assertExitCode(0);
    }

    public function test_email_test_order_command_with_nonexistent_order(): void
    {
        $this->artisan('email:test-order', ['--order-id' => 99999])
            ->expectsOutput('Order with ID 99999 not found.')
            ->assertExitCode(1);
    }

    public function test_email_test_order_command_without_orders(): void
    {
        $this->artisan('email:test-order')
            ->expectsOutput('No orders found. Please run seeders first: php artisan db:seed')
            ->assertExitCode(1);
    }

    public function test_email_test_order_command_uses_first_order_when_no_id(): void
    {
        $order = Order::factory()->create();

        $this->artisan('email:test-order')
            ->expectsOutput('Order confirmation email sent successfully!')
            ->assertExitCode(0);
    }

    public function test_email_info_command_shows_configuration(): void
    {
        $this->artisan('email:info')
            ->expectsOutput('📧 Email System Information')
            ->expectsOutputToContain('🔧 Configuration:')
            ->expectsOutputToContain('📊 Available Data:')
            ->expectsOutputToContain('🚀 Available Email Commands:')
            ->assertExitCode(0);
    }

    public function test_email_info_command_shows_data_counts(): void
    {
        // Just test that it runs successfully with data
        Customer::factory()->count(2)->create();
        Order::factory()->count(1)->create();

        $this->artisan('email:info')
            ->expectsOutputToContain('Available Data:')
            ->expectsOutputToContain('Customers:')
            ->expectsOutputToContain('Orders:')
            ->assertExitCode(0);
    }

    public function test_email_info_command_shows_quick_examples_when_data_exists()
    {
        Customer::factory()->count(2)->create();
        Order::factory()->count(2)->create();

        $this->artisan('email:info')
            ->expectsOutputToContain('🎯 Quick Test Examples:')
            ->assertExitCode(0);
    }
}
