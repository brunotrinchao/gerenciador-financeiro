<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TransactionItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transactions = Transaction::all();

        foreach ($transactions as $transaction) {
            $installments = $transaction->is_recurring ? $transaction->recurrence_interval : 1;

            // Valor base por parcela
            $baseAmount = floor(($transaction->amount / $installments) * 100) / 100;

            // Calcula o total atribuído e a sobra
            $totalAssigned = $baseAmount * $installments;
            $remainder = round($transaction->amount - $totalAssigned, 2);

            for ($i = 0; $i < $installments; $i++) {
                $dueDate = Carbon::parse($transaction->date)->addMonths($i);

                // Última parcela recebe o valor da sobra
                $amount = $i === $installments - 1 ? $baseAmount + $remainder : $baseAmount;

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'due_date' => $dueDate,
                    'amount' => $amount,
                    'installment_number' => $i + 1,
                    'status' => 'PENDING',
                ]);
            }
        }
    }
}
