<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Card;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $accounts = Account::all();
        $cards = Card::all();
        $categories = Category::all();

        $despesasFixas = [
            'Conta de luz',
            'Conta de água',
            'Internet banda larga',
            'Assinatura Netflix',
            'Assinatura Spotify',
            'Plano de saúde',
            'Mensalidade da escola',
            'Gasolina',
            'Supermercado',
            'Fatura do cartão de crédito',
            'Seguro do carro',
            'Prestação do carro',
            'Manutenção do carro',
            'IPTU',
            'IPVA'
        ];

        $receitasFixas = [
            'Salário',
            'Rendimento de investimentos',
            'Freelance de design',
            'Comissão de vendas',
            'Aluguel recebido',
            'Reembolso da empresa',
            'Lucros de negócio próprio',
            'Restituição de imposto de renda',
        ];

        $methods = ['CASH', 'ACCOUNT', 'CARD'];

        // Transações mensais variadas
        foreach (range(1, 12) as $month) {
            foreach (range(1, 3) as $i) {
                $isIncome = rand(0, 1);
                $description = $isIncome
                    ? $receitasFixas[array_rand($receitasFixas)]
                    : $despesasFixas[array_rand($despesasFixas)];

                Transaction::create([
                    'amount' => rand(100, 1000),
                    'description' => $description,
                    'date' => Carbon::create(null, $month, rand(1, 28)),
                    'is_recurring' => false,
                    'recurrence_interval' => 1,
                    'account_id' => $accounts->random()->id,
                    'category_id' => $categories->random()->id,
                    'user_id' => $users->random()->id,
                    'type' => $isIncome ? 'INCOME' : 'EXPENSE',
                    'method' => 'ACCOUNT',
                ]);
            }
        }

        // Transações recorrentes (ex: assinaturas, parcelamentos)
        foreach (range(1, 5) as $i) {
            $startMonth = rand(1, 6);
            $interval = rand(3, 6); // duração da recorrência
            $startDate = Carbon::create(null, $startMonth, rand(1, 28));

            Transaction::create([
                'amount' => rand(200, 800),
                'description' => $despesasFixas[array_rand($despesasFixas)],
                'date' => $startDate,
                'is_recurring' => true,
                'recurrence_interval' => $interval,
                'card_id' => $cards->random()->id,
                'category_id' => $categories->random()->id,
                'user_id' => $users->random()->id,
                'type' => 'EXPENSE',
                'method' => 'CARD',
            ]);
        }
    }
}
