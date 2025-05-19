<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\BrandCard;
use App\Models\Card;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first(); // Pega o primeiro usuário (Admin)
        $banks = Bank::all();
        $brandCards = BrandCard::all();

        // Criando cartões para o usuário Admin
        Card::create([
            'name' => 'Inter One',
            'brand_id' => $brandCards->random()->id,
            'number' => '1234 5678 9876 5432',
            'user_id' => $user->id,
            'bank_id' => $banks->random()->id,
            'due_date' => '15',
        ]);

        Card::create([
            'name' => 'Bradesco Exclusive',
            'brand_id' => $brandCards->random()->id,
            'number' => '4321 8765 6789 1234',
            'user_id' => $user->id,
            'bank_id' => $banks->random()->id,
            'due_date' => '10',
        ]);
    }
}
