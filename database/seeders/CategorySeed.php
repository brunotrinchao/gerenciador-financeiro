<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Receitas
            ['name' => 'Salário', 'type' => 'income'],
            ['name' => 'Freelance', 'type' => 'income'],
            ['name' => 'Rendimentos de investimentos', 'type' => 'income'],
            ['name' => 'Aluguel recebido', 'type' => 'income'],
            ['name' => 'Reembolso', 'type' => 'income'],
            ['name' => '13º salário', 'type' => 'income'],
            ['name' => 'Bonificação', 'type' => 'income'],
            ['name' => 'Outras receitas', 'type' => 'income'],

            // Despesas
            ['name' => 'Aluguel', 'type' => 'expense'],
            ['name' => 'Supermercado', 'type' => 'expense'],
            ['name' => 'Transporte', 'type' => 'expense'],
            ['name' => 'Combustível', 'type' => 'expense'],
            ['name' => 'Educação', 'type' => 'expense'],
            ['name' => 'Luz', 'type' => 'expense'],
            ['name' => 'Água', 'type' => 'expense'],
            ['name' => 'Internet', 'type' => 'expense'],
            ['name' => 'Telefone', 'type' => 'expense'],
            ['name' => 'Cartão de crédito', 'type' => 'expense'],
            ['name' => 'Lazer', 'type' => 'expense'],
            ['name' => 'Viagem', 'type' => 'expense'],
            ['name' => 'Saúde', 'type' => 'expense'],
            ['name' => 'Farmácia', 'type' => 'expense'],
            ['name' => 'Roupas', 'type' => 'expense'],
            ['name' => 'Assinaturas e serviços', 'type' => 'expense'],
            ['name' => 'Doações', 'type' => 'expense'],
            ['name' => 'Outras despesas', 'type' => 'expense'],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name']
            ]);
        }
    }
}
