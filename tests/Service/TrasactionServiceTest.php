<?php

namespace Service;

use App\Models\Account;
use App\Models\Bank;
use App\Models\BrandCard;
use App\Models\Card;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrasactionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TransactionService::class);
    }

    public function test_it_creates_simple_income_transaction_on_debit_account()
    {
        $user = User::factory()->create();
        $bank = Bank::factory()->create();


        $account = Account::factory()->create([
            'user_id' => $user->id,
            'bank_id' => $bank->id,
            'type' => 1,
            'balance' => 1000
        ]);

        $category = Category::factory()->create();

        $data = [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'INCOME',
            'method' => 'ACCOUNT',
            'amount' => 500,
            'account_id' => $account->id,
            'date' => now()->toDateString(),
            'description' => 'Test Transaction',
        ];

        $this->service->create($data);

        $account->refresh();
        $this->assertEquals(1000.00, $account->balance);

        $this->assertDatabaseCount(Transaction::class, 1);
        $this->assertDatabaseCount(TransactionItem::class, 1);
    }

    public function test_it_creates_expense_transaction_on_card_and_updates_limit()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $bank = Bank::factory()->create();
        $brad = BrandCard::factory()->create();

        $card = Card::factory()->create([
            'user_id' => $user->id,
            'bank_id' => $bank->id,
            'brand_id' => $brad->id,
            'limit' => 3000
        ]);

        $category = Category::factory()->create();

        $data = [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'EXPENSE',
            'method' => 'CARD',
            'amount' => 1200,
            'card_id' => $card->id,
            'date' => now()->toDateString(),
            'description' => 'Test Transaction',
        ];

        $this->service->create($data);
        $card->refresh();

        $this->assertEquals(3000, $card->limit); // 3000 - 1200

        $this->assertDatabaseCount(Transaction::class, 1);
        $this->assertDatabaseCount(TransactionItem::class, 1);
    }

    public function test_it_creates_recurring_transaction_items_correctly()
    {
        $user = User::factory()->create();
        $bank = Bank::factory()->create();


        $account = Account::factory()->create([
            'user_id' => $user->id,
            'bank_id' => $bank->id,
            'type' => 1,
            'balance' => 1000
        ]);

        $category = Category::factory()->create();

        $data = [
            'user_id' => $user->id,
            'type' => 'EXPENSE',
            'category_id' => $category->id,
            'method' => 'ACCOUNT',
            'amount' => 900,
            'account_id' => $account->id,
            'is_recurring' => true,
            'recurrence_interval' => 3,
            'paid_interval' => 1,
            'date' => '2025-07-01',
            'description' => 'Test Transaction',
        ];

        $this->service->create($data);

        $items = TransactionItem::all();
        $this->assertCount(3, $items);


        $account->refresh();

        $this->assertEquals('PAID', $items[0]->status);
        $this->assertEquals('DEBIT', $items[1]->status);
        $this->assertEquals('DEBIT', $items[2]->status);

        $this->assertEquals(700, $account->balance); // 900 / 3 => 300,00 => 30000 (em centavos)
    }

    public function test_it_sets_due_date_to_card_due_day_if_defined()
    {
        $user = User::factory()->create();

        $bank = Bank::factory()->create();
        $brad = BrandCard::factory()->create();

        $category = Category::factory()->create();

        $card = Card::factory()->create([
            'user_id' => $user->id,
            'bank_id' => $bank->id,
            'brand_id' => $brad->id,
            'limit' => 2000,
            'due_date' => 10,
        ]);

        $data = [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'EXPENSE',
            'method' => 'CARD',
            'amount' => 1000,
            'card_id' => $card->id,
            'date' => '2025-07-01',
            'is_recurring' => true,
            'recurrence_interval' => 2,
            'description' => 'Test Transaction',
        ];

        $this->service->create($data);

        $this->assertEquals(2000, $card->limit);

        $items = TransactionItem::all();
        $this->assertEquals('2025-07-10', Carbon::parse($items[0]->due_date)->toDateString());
        $this->assertEquals('2025-08-10', Carbon::parse($items[1]->due_date)->toDateString());
    }
}
