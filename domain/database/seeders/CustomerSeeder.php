<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Notifications\WelcomeCustomerNotification;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'João da Silva',
                'email' => 'joao.silva@email.com',
                'phone' => '(11) 99999-1234',
                'birth_date' => '1985-03-15',
                'address' => 'Rua das Flores, 123',
                'complement' => 'Apt 45',
                'neighborhood' => 'Centro',
                'zip_code' => '01234-567'
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria.santos@email.com',
                'phone' => '(11) 98888-5678',
                'birth_date' => '1990-07-22',
                'address' => 'Av. Paulista, 456',
                'complement' => null,
                'neighborhood' => 'Bela Vista',
                'zip_code' => '01310-100'
            ],
            [
                'name' => 'Pedro Oliveira',
                'email' => 'pedro.oliveira@email.com',
                'phone' => '(11) 97777-9999',
                'birth_date' => '1988-12-10',
                'address' => 'Rua Augusta, 789',
                'complement' => 'Sala 12',
                'neighborhood' => 'Consolação',
                'zip_code' => '01305-000'
            ],
            [
                'name' => 'Ana Costa',
                'email' => 'ana.costa@email.com',
                'phone' => '(11) 96666-1111',
                'birth_date' => '1995-05-08',
                'address' => 'Rua Oscar Freire, 321',
                'complement' => null,
                'neighborhood' => 'Jardins',
                'zip_code' => '01426-001'
            ],
            [
                'name' => 'Carlos Ferreira',
                'email' => 'carlos.ferreira@email.com',
                'phone' => '(11) 95555-2222',
                'birth_date' => '1982-11-30',
                'address' => 'Rua da Consolação, 654',
                'complement' => 'Bloco B',
                'neighborhood' => 'República',
                'zip_code' => '01302-001'
            ]
        ];

        // Only show output when not testing
        if (app()->environment() !== 'testing') {
            echo "Clientes criados com sucesso!" . PHP_EOL;
        }

        foreach ($customers as $customerData) {
            $customer = Customer::create($customerData);
            
            // Send welcome email to predefined customers (only in non-testing environments)
            if (app()->environment() !== 'testing') {
                try {
                    $customer->notify(new WelcomeCustomerNotification($customer));
                    echo "✅ Email de boas-vindas enviado para: {$customer->name}" . PHP_EOL;
                } catch (\Exception $e) {
                    echo "❌ Erro ao enviar email para {$customer->name}: {$e->getMessage()}" . PHP_EOL;
                }
            }
        }

        // Create additional random customers (without sending emails to avoid spam)
        Customer::factory()->count(15)->create();
    }
}
