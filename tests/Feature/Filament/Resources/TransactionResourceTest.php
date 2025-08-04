<?php

namespace Feature\Filament\Resources;

use App\Enum\TransactionTypeEnum;
use App\Filament\Resources\TransactionResource;
use App\Filament\Resources\TransactionResource\Pages\ListTransactions;
use App\Models\Account;
use App\Models\Bank;
use App\Models\BrandCard;
use App\Models\Card;
use App\Models\Category;
use App\Models\Family;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;

class TransactionResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Family::factory()->create(['id' => 1]);
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_render_transaction_page(): void
    {
        $this->get(TransactionResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_required_fields_validation(): void
    {
        $response = Livewire::test(ListTransactions::class)
            ->callTableAction('createTransaction', data: [
                'type' => 'INCOME',
                'method' => 'ACCOUNT',
            ]);

        $response->assertHasTableActionErrors([
                'category_id' => 'required',
                'description' => 'required',
                'amount' => 'required',
            ]);
    }

    public function test_can_create_simple_transaction(): void
    {
        $bank = Bank::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $this->user->id,
            'bank_id' => $bank->id,
        ]);
        $category = Category::factory()->create();

        Livewire::test(ListTransactions::class)
            ->callTableAction('createTransaction', data: [
                'type' => TransactionTypeEnum::INCOME->name,
                'method' => 'ACCOUNT',
                'category_id' => $category->id,
                'account_id' => $account->id,
                'description' => 'Salário',
                'amount' => '1.000,00',
                'date' => now()->format('Y-m-d'),
                'user_id' => $this->user->id,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('transactions', [
            'description' => 'Salário',
            'account_id' => $account->id,
        ]);
    }

    public function test_can_create_transaction_with_card(): void
    {
        $bank = Bank::factory()->create();
        $brand = BrandCard::factory()->create();
        $card = Card::factory()->create([
            'user_id' => $this->user->id,
            'bank_id' => $bank->id,
            'brand_id' => $brand->id,
        ]);
        $category = Category::factory()->create();

        Livewire::test(ListTransactions::class)
            ->callTableAction('createTransaction', data: [
                'type' => TransactionTypeEnum::EXPENSE->name,
                'method' => 'CARD',
                'category_id' => $category->id,
                'card_id' => $card->id,
                'description' => 'Compra online',
                'amount' => '500,00',
                'date' => now()->format('Y-m-d'),
                'user_id' => $this->user->id,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('transactions', [
            'description' => 'Compra online',
            'card_id' => $card->id,
        ]);
    }

    public function test_can_create_transfer_account_to_account(): void
    {
        $bank = Bank::factory()->create();
        $origin = Account::factory()->create([
            'user_id' => $this->user->id,
            'bank_id' => $bank->id,
        ]);
        $target = Account::factory()->create([
            'user_id' => $this->user->id,
            'bank_id' => $bank->id,
        ]);
        $category = Category::factory()->create();

        Livewire::test(ListTransactions::class)
            ->callTableAction('createTransaction', data: [
                'type' => TransactionTypeEnum::TRANSFER->name,
                'method' => 'ACCOUNT',
                'category_id' => $category->id,
                'origin_account_id' => $origin->id,
                'target_account_id' => $target->id,
                'description' => 'Transferência interna',
                'amount' => '250,00',
                'date' => now()->format('Y-m-d'),
                'user_id' => $this->user->id,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('transactions', [
            'account_id' => $origin->id,
            'description' => "Origem: {$origin->bank->name}. | Transferência interna",
        ]);

        $this->assertDatabaseHas('transactions', [
            'account_id' => $target->id,
            'description' => "Destino: {$origin->bank->name}. | Transferência interna",
        ]);

    }

    public function test_can_create_cash_transfer(): void
    {
        $bank = Bank::factory()->create();
        $target = Account::factory()->create([
            'user_id' => $this->user->id,
            'bank_id' => $bank->id,
        ]);
        $category = Category::factory()->create();

        Livewire::test(ListTransactions::class)
            ->callTableAction('createTransaction', data: [
                'type' => TransactionTypeEnum::TRANSFER->name,
                'method' => 'CASH',
                'category_id' => $category->id,
                'target_account_id' => $target->id,
                'description' => 'Depósito em conta',
                'amount' => '200,00',
                'date' => now()->format('Y-m-d'),
                'user_id' => $this->user->id,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('transactions', [
            'account_id' => $target->id,
            'description' => "Destino: {$target->bank->name}. | Depósito em conta",
        ]);
    }

    public function test_can_create_recurring_transaction_with_paid_installments(): void
    {
        $bank = Bank::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $this->user->id,
            'bank_id' => $bank->id,
        ]);
        $category = Category::factory()->create();

        Livewire::test(ListTransactions::class)
            ->callTableAction('createTransaction', data: [
                'type' => TransactionTypeEnum::EXPENSE->name,
                'method' => 'ACCOUNT',
                'category_id' => $category->id,
                'account_id' => $account->id,
                'description' => 'Curso parcelado',
                'amount' => '900,00',
                'date' => now()->format('Y-m-d'),
                'is_recurring' => true,
                'recurrence_interval' => 3,
                'paid_interval' => 1,
                'user_id' => $this->user->id,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('transactions', [
            'description' => 'Curso parcelado',
            'recurrence_interval' => 3,
        ]);
    }

    public function test_can_edit_transaction(): void
    {
        $bank = Bank::factory()->create();
        $account = Account::factory()->create(['user_id' => $this->user->id, 'bank_id' => $bank->id]);
        $category = Category::factory()->create();


        $transaction = Livewire::test(ListTransactions::class)
            ->callTableAction('createTransaction', data: [
                'type' => TransactionTypeEnum::EXPENSE->name,
                'method' => 'ACCOUNT',
                'category_id' => $category->id,
                'account_id' => $account->id,
                'description' => 'Curso parcelado',
                'amount' => 100,
                'date' => now()->format('Y-m-d'),
                'is_recurring' => false,
                'user_id' => $this->user->id,
            ]);

        $transaction = Transaction::where('description', 'Curso parcelado')->firstOrFail();
        $transaction->load('items');

        Livewire::test(ListTransactions::class)
            ->callTableAction('editTransaction', record: $transaction, data: [
                'type' => TransactionTypeEnum::INCOME->name,
                'method' => 'ACCOUNT',
                'account_id' => $account->id,
                'category_id' => $category->id,
                'amount' => 150.00,
                'description' => 'Atualizada via teste',
                'date' => now()->format('Y-m-d'),
                'is_recurring' => false,
                'user_id' => $this->user->id,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'description' => 'Atualizada via teste',
            'amount' => 150.00,
        ]);
    }



}
