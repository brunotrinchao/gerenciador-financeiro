<?php

namespace Feature\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages\CreateAccount;
use App\Filament\Resources\AccountResource\Pages\EditAccount;
use App\Filament\Resources\AccountResource\Pages\ListAccounts;
use App\Filament\Resources\TransactionResource\Pages\ListTransactions;
use App\Models\Bank;
use App\Models\User;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;

class AccountResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_required_fields_validation_fails_for_account(): void
    {
        $response = Livewire::test(ListAccounts::class)
            ->callTableAction('createAccount', data: [
                'type' => '',
                'bank_id' => '',
                'balance' => '',
            ]);

        $response->assertHasTableActionErrors([
                'type' => 'required',
                'bank_id' => 'required',
                'balance' => 'required',
            ]);

    }

    public function test_can_create_account(): void
    {
        $bank = \App\Models\Bank::factory()->create();

        $response = Livewire::test(ListAccounts::class)
            ->callTableAction('createAccount', data: [
                'type' => 1,
                'bank_id' => $bank->id,
                'balance' => 1000,
            ]);
        $response->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('accounts', [
            'type' => 1,
            'bank_id' => $bank->id,
            'balance' => 1000,
        ]);
    }

    public function test_type_validation_fails_with_invalid_value(): void
    {

        Livewire::test(ListAccounts::class)
            ->callTableAction('createAccount', data: [
                'user_id' => $this->user->id,
                'type' => null,
                'bank_id' => null,
                'balance' => null,
            ])
            ->assertHasTableActionErrors([
                'type' => 'required',
                'bank_id' => 'required',
                'balance' => 'required',
            ]);
    }

    public function test_can_update_account(): void
    {
        $bank = Bank::factory()->create();

        $account = Account::factory()->create([
            'user_id' => $this->user->id,
            'type' => 1,
            'bank_id' => $bank->id,
            'balance' => 500,
        ]);

        Livewire::test(ListAccounts::class)
            ->callTableAction('editAccount', record: $account, data: [
                'type' => 2,
                'bank_id' => $bank->id,
                'balance' => 750,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'type' => 2,
            'balance' => 750,
        ]);
    }

}
