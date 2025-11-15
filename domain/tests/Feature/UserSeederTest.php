<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_seeder_creates_admin_users(): void
    {
        $this->seed(UserSeeder::class);

        $this->assertDatabaseHas('users', [
            'name' => 'Administrador da Pastelaria',
            'email' => 'admin@pastelaria.com'
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Gerente de Vendas',
            'email' => 'vendas@pastelaria.com'
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Atendente',
            'email' => 'atendente@pastelaria.com'
        ]);
    }

    public function test_user_seeder_creates_correct_number_of_users(): void
    {
        $this->seed(UserSeeder::class);

        $this->assertEquals(8, User::count());
    }

    public function test_admin_users_have_verified_emails(): void
    {
        $this->seed(UserSeeder::class);

        $adminUser = User::where('email', 'admin@pastelaria.com')->first();
        $this->assertNotNull($adminUser->email_verified_at);

        $salesUser = User::where('email', 'vendas@pastelaria.com')->first();
        $this->assertNotNull($salesUser->email_verified_at);

        $attendantUser = User::where('email', 'atendente@pastelaria.com')->first();
        $this->assertNotNull($attendantUser->email_verified_at);
    }

    public function test_admin_users_can_login_with_default_password(): void
    {
        $this->seed(UserSeeder::class);

        $adminUser = User::where('email', 'admin@pastelaria.com')->first();
        $this->assertTrue(Hash::check('password123', $adminUser->password));

        $salesUser = User::where('email', 'vendas@pastelaria.com')->first();
        $this->assertTrue(Hash::check('password123', $salesUser->password));
    }
}