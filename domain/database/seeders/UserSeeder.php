<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin users for the pastry shop
        $users = [
            [
                'name' => 'Administrador da Pastelaria',
                'email' => 'admin@pastelaria.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now()
            ],
            [
                'name' => 'Gerente de Vendas',
                'email' => 'vendas@pastelaria.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now()
            ],
            [
                'name' => 'Atendente',
                'email' => 'atendente@pastelaria.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now()
            ]
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        // Create additional test users
        User::factory()->count(5)->create();
    }
}
