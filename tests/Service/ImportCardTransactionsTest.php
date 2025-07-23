<?php
namespace Service;

use App\Filament\Resources\CardResource\Pages\ImportCardTransactions;
use App\Models\Bank;
use App\Models\BrandCard;
use App\Models\Card;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ImportCardTransactionsTest extends TestCase
{
    use RefreshDatabase;

    private Collection $transactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_import_creates_transaction_and_items()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $bank = Bank::factory()->create();

        $brad = BrandCard::factory()->create();

        $card = Card::factory()->create([
            'user_id' => $user->id,
            'bank_id' => $bank->id,
            'brand_id' => $brad->id,
        ]);

        Category::factory()->create(['name' => 'Cartão de crédito']);

        $transactions = [[
            'description' => 'HNA*OBOTICARIO',
            'total_amount' => '396.80',
            'month' => '05',
            'year' => '2025',
            'total_installments' => 5,
            'paid_installments' => 2,
        ]];


        Livewire::test(ImportCardTransactions::class)
            ->set('selectedCard', $card)
            ->set('transactions', $transactions)
            ->call('import');

        $this->assertDatabaseHas('transactions', [
            'description' => 'HNA*OBOTICARIO',
            'card_id' => $card->id,
            'recurrence_interval' => 5,
            'amount' => 39680,
            'user_id' => $user->id,
        ]);

        $transaction = Transaction::where('description', 'HNA*OBOTICARIO')->first();

        $this->assertDatabaseCount('transaction_items', 5);

        $items = TransactionItem::where('transaction_id', $transaction->id)->get();

        $this->assertEquals('PAID', $items[0]->status);
        $this->assertEquals('PAID', $items[1]->status);
        $this->assertEquals('DEBIT', $items[2]->status);
        $this->assertEquals('DEBIT', $items[3]->status);
        $this->assertEquals('DEBIT', $items[4]->status);

        $expectedAmount = (int) 39680 / 5; // centavos
        foreach ($items as $item) {
            $this->assertEquals($expectedAmount, $item->amount);
        }
    }

}
