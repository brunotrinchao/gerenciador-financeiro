<?php

namespace Feature\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages\CreateAccount;
use App\Filament\Resources\AccountResource\Pages\EditAccount;
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
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateAccount::class)
            ->fillForm([
                'type' => '',
                'bank_id' => '',
                'balance' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'type' => 'required',
                'bank_id' => 'required',
                'balance' => 'required',
            ]);
    }

    public function test_can_create_account(): void
    {
        $bank = \App\Models\Bank::factory()->create();

        Livewire::actingAs($this->user)
            ->test(CreateAccount::class)
            ->fillForm([
                'type' => 'CHECKING',
                'bank_id' => $bank->id,
                'balance' => 1000,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('accounts', [
            'type' => 'CHECKING',
            'bank_id' => $bank->id,
            'balance' => 1000,
        ]);
    }

    public function test_type_validation_fails_with_invalid_value(): void
    {

        Livewire::actingAs($this->user)
            ->test(CreateAccount::class)
            ->fillForm([
                'user_id' => $this->user->id,
                'type' => null,
                'bank_id' => null,
                'balance' => null,
            ])
            ->call('create')
            ->assertHasErrors([
                'data.type' => 'required',
                'data.bank_id' => 'required',
                'data.balance' => 'required',
            ]);
    }

    public function test_can_update_account(): void
    {
        $bank = Bank::factory()->create();

        $account = Account::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'SAVINGS',
            'bank_id' => $bank->id,
            'balance' => 500,
        ]);

        Livewire::actingAs($this->user)
            ->test(EditAccount::class, [
                'record' => $account->getKey(),
            ])
            ->fillForm([
                'type' => 'CHECKING',
                'bank_id' => $bank->id,
                'balance' => 750,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'type' => 'CHECKING',
            'balance' => 750,
        ]);
    }

}
