<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Bank;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Criando contas para o usuário Admin
        Bank::create([
            'name' => 'Banco do Brasil',
            'code' => random_int(100, 999),
        ]);

        Bank::create([
            'name' => 'Bradesco',
            'code' => random_int(100, 999),
        ]);

        Bank::create([
            'name' => 'Caixa Econômica',
            'code' => random_int(100, 999),
        ]);
    }
}
