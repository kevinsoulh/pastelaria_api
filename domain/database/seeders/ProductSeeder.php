<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Pastel de Carne',
                'description' => 'Pastel crocante recheado com carne moída temperada',
                'price' => 8.50,
                'category' => 'salgado',
                'is_available' => true,
                'photo' => 'pastels/pastel_carne.jpg'
            ],
            [
                'name' => 'Pastel de Queijo',
                'description' => 'Pastel tradicional com queijo mussarela derretida',
                'price' => 7.00,
                'category' => 'salgado',
                'is_available' => true,
                'photo' => 'pastels/pastel_queijo.jpg'
            ],
            [
                'name' => 'Pastel de Frango',
                'description' => 'Pastel recheado com frango desfiado temperado',
                'price' => 9.00,
                'category' => 'salgado',
                'is_available' => true,
                'photo' => 'pastels/pastel_frango.jpg'
            ],
            [
                'name' => 'Pastel de Camarão',
                'description' => 'Pastel gourmet com camarão refogado',
                'price' => 15.00,
                'category' => 'especial',
                'is_available' => true,
                'photo' => 'pastels/pastel_camarao.jpg'
            ],
            [
                'name' => 'Pastel de Palmito',
                'description' => 'Pastel vegetariano com palmito refogado',
                'price' => 10.50,
                'category' => 'salgado',
                'is_available' => true,
                'photo' => 'pastels/pastel_palmito.jpg'
            ],
            [
                'name' => 'Pastel de Pizza',
                'description' => 'Pastel com recheio de pizza: molho, queijo e oregano',
                'price' => 11.00,
                'category' => 'salgado',
                'is_available' => true,
                'photo' => 'pastels/pastel_pizza.jpg'
            ],
            [
                'name' => 'Pastel de Chocolate',
                'description' => 'Pastel doce recheado com chocolate cremoso',
                'price' => 6.50,
                'category' => 'doce',
                'is_available' => true,
                'photo' => 'pastels/pastel_chocolate.jpg'
            ],
            [
                'name' => 'Pastel de Banana',
                'description' => 'Pastel doce com banana e canela',
                'price' => 6.00,
                'category' => 'doce',
                'is_available' => true,
                'photo' => 'pastels/pastel_banana.jpg'
            ],
            [
                'name' => 'Pastel Misto',
                'description' => 'Pastel com queijo, presunto e oregano',
                'price' => 12.00,
                'category' => 'salgado',
                'is_available' => true,
                'photo' => 'pastels/pastel_misto.jpg'
            ],
            [
                'name' => 'Pastel Especial da Casa',
                'description' => 'Pastel gourmet com recheio especial da casa',
                'price' => 18.00,
                'category' => 'especial',
                'is_available' => true,
                'photo' => 'pastels/pastel_especial.jpg'
            ],
            [
                'name' => 'Mini Pastel de Queijo (6 unidades)',
                'description' => 'Porção com 6 mini pastéis de queijo',
                'price' => 14.00,
                'category' => 'especial',
                'is_available' => true,
                'photo' => 'pastels/mini_pasteis.jpg'
            ],
            [
                'name' => 'Pastel Doce de Leite',
                'description' => 'Pastel doce recheado com doce de leite cremoso',
                'price' => 7.50,
                'category' => 'doce',
                'is_available' => true,
                'photo' => 'pastels/pastel_doce_leite.jpg'
            ],
            [
                'name' => 'Pastel de Bacalhau',
                'description' => 'Pastel gourmet com bacalhau desfiado',
                'price' => 16.50,
                'category' => 'especial',
                'is_available' => true,
                'photo' => 'pastels/pastel_bacalhau.jpg'
            ],
            [
                'name' => 'Pastel Vegetariano',
                'description' => 'Pastel com mix de vegetais refogados',
                'price' => 9.50,
                'category' => 'salgado',
                'is_available' => true,
                'photo' => 'pastels/pastel_vegetariano.jpg'
            ]
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }
    }
}
