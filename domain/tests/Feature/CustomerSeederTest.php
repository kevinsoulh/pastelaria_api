<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use Database\Seeders\CustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_seeder_creates_predefined_customers(): void
    {
        $this->seed(CustomerSeeder::class);

        $this->assertDatabaseHas('customers', [
            'name' => 'João da Silva',
            'email' => 'joao.silva@email.com',
            'phone' => '(11) 99999-1234'
        ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'Maria Santos',
            'email' => 'maria.santos@email.com'
        ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'Pedro Oliveira',
            'email' => 'pedro.oliveira@email.com'
        ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'Ana Costa',
            'email' => 'ana.costa@email.com'
        ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'Carlos Ferreira',
            'email' => 'carlos.ferreira@email.com'
        ]);
    }

    public function test_customer_seeder_creates_correct_number_of_customers(): void
    {
        $this->seed(CustomerSeeder::class);

        $this->assertEquals(20, Customer::count());
    }

    public function test_customer_seeder_creates_customers_with_required_fields(): void
    {
        $this->seed(CustomerSeeder::class);

        $customer = Customer::where('email', 'joao.silva@email.com')->first();
        
        $this->assertNotNull($customer->name);
        $this->assertNotNull($customer->email);
        $this->assertNotNull($customer->phone);
        $this->assertNotNull($customer->address);
        $this->assertNotNull($customer->neighborhood);
        $this->assertNotNull($customer->zip_code);
        $this->assertEquals('1985-03-15', $customer->birth_date->format('Y-m-d'));
    }

    public function test_customer_seeder_handles_optional_fields(): void
    {
        $this->seed(CustomerSeeder::class);

        $joao = Customer::where('email', 'joao.silva@email.com')->first();
        $this->assertEquals('Apt 45', $joao->complement);

        $maria = Customer::where('email', 'maria.santos@email.com')->first();
        $this->assertNull($maria->complement);
    }

    public function test_all_customer_emails_are_unique(): void
    {
        $this->seed(CustomerSeeder::class);

        $emails = Customer::pluck('email');
        $uniqueEmails = $emails->unique();

        $this->assertEquals($emails->count(), $uniqueEmails->count());
    }
}