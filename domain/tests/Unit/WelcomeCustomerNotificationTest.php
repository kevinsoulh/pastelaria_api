<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Notifications\WelcomeCustomerNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomeCustomerNotificationTest extends TestCase
{
    use RefreshDatabase;
    public function test_notification_uses_mail_channel(): void
    {
        $customer = new Customer(['name' => 'John Doe']);
        $notification = new WelcomeCustomerNotification($customer);
        
        $channels = $notification->via($customer);
        
        $this->assertContains('mail', $channels);
    }

    public function test_notification_to_array()
    {
        $customer = Customer::factory()->create([
            'name' => 'Test Customer'
        ]);

        $notification = new WelcomeCustomerNotification($customer);
        $array = $notification->toArray($customer);

        $this->assertArrayHasKey('customer_id', $array);
        $this->assertArrayHasKey('customer_name', $array);
        $this->assertEquals($customer->id, $array['customer_id']);
        $this->assertEquals('Test Customer', $array['customer_name']);
    }

    public function test_notification_to_mail()
    {
        $customer = Customer::factory()->create([
            'name' => 'João Silva'
        ]);

        $notification = new WelcomeCustomerNotification($customer);
        $mailMessage = $notification->toMail($customer);

        $this->assertInstanceOf(\Illuminate\Notifications\Messages\MailMessage::class, $mailMessage);
        $this->assertEquals('Bem-vindo à Pastelaria!', $mailMessage->subject);
        $this->assertStringContainsString('Olá João Silva!', $mailMessage->greeting);
        $this->assertStringContainsString('Seja bem-vindo(a) à nossa pastelaria!', $mailMessage->introLines[0]);
    }

    public function test_notification_constructor_sets_customer()
    {
        $customer = Customer::factory()->create();
        $notification = new WelcomeCustomerNotification($customer);

        $reflectionClass = new \ReflectionClass($notification);
        $customerProperty = $reflectionClass->getProperty('customer');
        $customerProperty->setAccessible(true);
        
        $this->assertEquals($customer->id, $customerProperty->getValue($notification)->id);
    }
}
