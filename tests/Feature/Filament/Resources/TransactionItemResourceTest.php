<?php

namespace Feature\Filament\Resources;

use App\Enum\TransactionTypeEnum;
use App\Filament\Resources\TransactionItemResource\Pages\ListTransactionItems;
use App\Filament\Resources\TransactionResource\Pages\ListTransactions;
use App\Filament\Resources\TransactionResource\RelationManagers\ItemsRelationManager;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Category;
use App\Models\Family;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\TransactionItemService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;

class TransactionItemResourceTest extends TestCase
{
    use RefreshDatabase;
    protected User $user;
    protected Family $family;

    protected function setUp(): void
    {
        parent::setUp();
        $this->family = Family::factory()->create(['id' => 1]);
        $this->user = User::factory()->create(['family_id' => $this->family->id]);
        $this->actingAs($this->user);
    }

    public function test_edit_transaction_item_within_allowed_amount(): void
    {
        $bank = Bank::factory()->create(['family_id' => $this->family->id]);
        $account = Account::factory()->create([
            'bank_id' => $bank->id,
            'user_id' => $this->user->id,
            'family_id' => $this->family->id]);
        $category = Category::factory()->create(['family_id' => $this->family->id]);

        $transactionData = [
            'type' => TransactionTypeEnum::EXPENSE->name,
            'method' => 'ACCOUNT',
            'category_id' => $category->id,
            'account_id' => $account->id,
            'description' => 'Compra parcelada',
            'amount' => 30000,
            'date' => Carbon::now()->format('Y-m-d'),
            'is_recurring' => true,
            'recurrence_interval' => 3,
            'recurrence_type' => 'MONTHLY',
            'user_id' => $this->user->id,
        ];

        Livewire::test(ListTransactions::class)
            ->callTableAction('createTransaction', data: $transactionData)
            ->assertHasNoTableActionErrors();

        $transaction = Transaction::where('description', 'Compra parcelada')->first();
        $item = $transaction->items()->first();

        $response = Livewire::test(ItemsRelationManager::class, [
            'ownerRecord' => $transaction,
            'pageClass' => ListTransactions::class,
        ])
            ->callTableAction('editTransactionItem', $item, data: [
                'amount' => 20000,
                'due_date' => Carbon::parse($item->due_date)->format('Y-m-d'),
                'payment_date' => Carbon::parse($item->due_date)->format('Y-m-d'),
                'method' => $transaction->method,
                'status' => 'PAID',
            ]);
            $response->assertHasNoTableActionErrors(['amount']);
        $item->refresh();

        $this->assertDatabaseHas('transaction_items', [
            'id' => $item->id,
            'amount' => 20000,
            'status' => 'PAID',
        ]);

        $items = TransactionItem::where('id', '!=', $item->id)->get();

        $amountCalc = $transaction->amount - $item->amount;
        $this->assertEquals($amountCalc, $items->sum('amount'));

    }

    public function test_transaction_items_are_created_correctly(): void
    {
        $bank = Bank::factory()->create();
        $account = Account::factory()->create(['bank_id' => $bank->id, 'user_id' => $this->user->id]);
        $category = Category::factory()->create();

        // Cria transação com 3 parcelas
        $transactionData = [
            'type' => TransactionTypeEnum::EXPENSE->name,
            'method' => 'ACCOUNT',
            'category_id' => $category->id,
            'account_id' => $account->id,
            'description' => 'Curso parcelado',
            'amount' => 300,
            'date' => now()->format('Y-m-d'),
            'is_recurring' => true,
            'recurrence_interval' => 3,
            'recurrence_type' => 'MONTHLY',
            'user_id' => $this->user->id,
        ];

        Livewire::test(ListTransactions::class)
            ->callTableAction('createTransaction', data: $transactionData)
            ->assertHasNoTableActionErrors();

        $transaction = Transaction::where('description', 'Curso parcelado')->first();
        $this->assertNotNull($transaction);

        $items = TransactionItem::where('transaction_id', $transaction->id)->get();
        $this->assertCount(3, $items);

        // Valida valores
        $this->assertEquals(100, $items[0]->amount);
        $this->assertEquals(1, $items[0]->installment_number);
        $this->assertEquals('DEBIT', $items[0]->status);
    }


    public function test_edit_transaction_item_respects_transaction_amount_limit(): void
    {
        $bank = Bank::factory()->create();
        $account = Account::factory()->create([
            'bank_id' => $bank->id,
            'user_id' => $this->user->id,
        ]);
        $category = Category::factory()->create();

        // Cria transação com 3 parcelas de R$ 100, total R$ 300
        $transactionData = [
            'type' => TransactionTypeEnum::EXPENSE->name,
            'method' => 'ACCOUNT',
            'category_id' => $category->id,
            'account_id' => $account->id,
            'description' => 'Curso parcelado',
            'amount' => 30000, // centavos
            'date' => Carbon::now()->format('Y-m-d'),
            'is_recurring' => true,
            'recurrence_interval' => 3,
            'recurrence_type' => 'MONTHLY',
            'user_id' => $this->user->id,
        ];

        Livewire::test(ListTransactions::class)
            ->callTableAction('createTransaction', data: $transactionData)
            ->assertHasNoTableActionErrors();

        $transaction = Transaction::where('description', 'Curso parcelado')->first();
        $this->assertNotNull($transaction);

        // Marca uma das parcelas como paga (não será editada)
        $paidItem = $transaction->items()->first();
        $paidItem->update([
            'status' => 'PAID',
            'payment_date' => Carbon::now(),
        ]);

        // Seleciona uma das pendentes
        $editableItem = $transaction->items()->where('status', '!=', 'PAID')->first();

        // Tenta editar com valor acima do permitido
        $response = Livewire::test(ItemsRelationManager::class, [
            'ownerRecord' => $transaction,
            'pageClass' => ListTransactions::class,
        ])
            ->callTableAction('editTransactionItem', $editableItem, data: [
                'amount' => 25000, // R$250, maior que o restante permitido
                'due_date' => Carbon::parse($editableItem->due_date)->format('Y-m-d'),
                'payment_date' => Carbon::parse($editableItem->due_date)->format('Y-m-d'),
                'method' => $transaction->method,
                'status' => 'PAID',
            ]);
        $errors = $response->errors()->toArray();

        $this->assertArrayHasKey('amount', $errors);
        $this->assertContains(
            'O valor da parcela não pode ser maior que o valor restante da transação (R$ 200,00).',
            $errors['amount']
        );

    }


}
