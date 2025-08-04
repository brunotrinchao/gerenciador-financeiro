<?php

namespace Feature\Filament\Resources;

use App\Filament\Resources\BankResource;
use App\Filament\Resources\BankResource\Pages\CreateBank;
use App\Filament\Resources\BankResource\Pages\ListBanks;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;

class BankResourceTest extends TestCase
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
    public function test_can_render_bank_create_page(): void
    {
        $this->actingAs($this->user)
            ->get(BankResource::getUrl('create'))
            ->assertSuccessful();
    }

    public function test_bank_form_requires_name_and_code(): void
    {
        Livewire::test(CreateBank::class)
            ->fillForm([
                'name' => '',
                'code' => '',
            ])
            ->call('create')
            ->assertHasErrors([
                'data.name' => 'required',
                'data.code' => 'required',
            ]);
    }

    public function test_bank_code_must_be_numeric(): void
    {

        Livewire::test(ListBanks::class)
            ->callTableAction('createBank', data: [
                'name' => 'Banco Teste',
                'code' => 'abc',
            ])
            ->assertHasTableActionErrors(['code']);
    }

    public function test_can_create_bank(): void
    {
        Livewire::test(ListBanks::class)
            ->callTableAction('createBank', data: [
                'name' => 'Banco do Brasil',
                'code' => '001',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('banks', [
            'name' => 'Banco do Brasil',
            'code' => '001',
        ]);
    }

}
