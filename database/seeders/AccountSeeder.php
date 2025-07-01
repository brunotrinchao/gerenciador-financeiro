<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Bank;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first(); // Pega o primeiro usuário (Admin)
        $banks = Bank::all();

        // Criando contas para o usuário Admin
        Account::create([
            'type' => 1,
            'user_id' => $user->id,
            'bank_id' => $banks->random()->id,
        ]);

        Account::create([
            'type' => 2,
            'user_id' => $user->id,
            'bank_id' => $banks->random()->id
        ]);
    }
}
