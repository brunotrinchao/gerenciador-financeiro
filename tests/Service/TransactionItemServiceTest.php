<?php

namespace Service;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionItemServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_recalculates_amount_for_remaining_transaction_items()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        // Valor total da transação: R$ 300,00 (30000 centavos)
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'EXPENSE',
            'amount' => 30000,
            'method' => 'CASH',
            'date' => now(),
            'recurrence_interval' => 3,
        ]);


        $baseValue = intdiv($transaction->amount, $transaction->recurrence_interval);
        $difference = $transaction->amount - ($baseValue * $transaction->recurrence_interval);

        for ($i = 0; $i < $transaction->recurrence_interval; $i++) {
            $amount = $i === $transaction->recurrence_interval - 1 ? $baseValue + $difference : $baseValue;

            TransactionItem::factory()->create([
                'transaction_id' => $transaction->id,
                'status' => 'PEDENT',
                'amount' => $amount,
            ]);
        }

        $firstItem = $transaction->items()->first();

        $firstItem->update(['status' => 'PAID']);


        $service = new \App\Services\TransactionItemService(); // ou onde o método estiver
        $service->recalcAmountTransactionItem($firstItem);

        $transaction->items->each(function ($item) use ($baseValue) {
            $this->assertEquals($baseValue, $item->amount);
        });

    }

    public function test_it_recalculates_amount_for_remaining_transaction_items_with_non_exact_installments()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        // Valor total da transação: R$ 301,00 (30100 centavos) dividido em 3 parcelas
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'EXPENSE',
            'amount' => 30100, // valor não divisível por 3
            'method' => 'CASH',
            'date' => now(),
            'recurrence_interval' => 3,
        ]);

        $baseValue = intdiv($transaction->amount, $transaction->recurrence_interval); // 10033
        $difference = $transaction->amount - ($baseValue * $transaction->recurrence_interval); // 1

        // Criando 3 itens com o último contendo o valor adicional (difference)
        for ($i = 0; $i < $transaction->recurrence_interval; $i++) {
            $amount = $i === $transaction->recurrence_interval - 1
                ? $baseValue + $difference
                : $baseValue;

            TransactionItem::factory()->create([
                'transaction_id' => $transaction->id,
                'status' => 'PEDENT',
                'amount' => $amount,
            ]);
        }

        // Marca o primeiro como pago
        $firstItem = $transaction->items()->first();
        $firstItem->update(['status' => 'PAID']);

        // Recalcula valores
        $service = new \App\Services\TransactionItemService();
        $service->recalcAmountTransactionItem($firstItem);

        $items = $transaction->fresh()->items;
        $expectedAmounts = [
            $baseValue,            // 10033
            $baseValue,            // 10033
            $baseValue + $difference // 10034
        ];

        foreach ($items as $index => $item) {
            $this->assertEquals(
                $expectedAmounts[$index],
                $item->amount,
                "Parcela {$index} esperada: {$expectedAmounts[$index]}, atual: {$item->amount}"
            );
        }
    }

}
