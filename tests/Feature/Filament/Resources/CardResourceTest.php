<?php

namespace Feature\Filament\Resources;

use App\Filament\Resources\CardResource;
use App\Filament\Resources\CardResource\Pages\ListCards;
use App\Models\Bank;
use App\Models\BrandCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;

class CardResourceTest extends TestCase
{
    use RefreshDatabase;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_render_card_list_page(): void
    {
        $this->actingAs($this->user)
            ->get(CardResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_can_create_card(): void
    {
        $bank = Bank::factory()->create();
        $brand = BrandCard::factory()->create();

        Livewire::test(ListCards::class)
            ->callTableAction('createCard', data: [
                'bank_id' => $bank->id,
                'name' => 'Cartão Teste',
                'number' => '1234 5678 9012 3456',
                'brand_id' => $brand->id,
                'due_date' => 10,
                'limit' => '1500,00',
            ])
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cards', [
            'name' => 'Cartão Teste',
            'bank_id' => $bank->id,
            'brand_id' => $brand->id,
        ]);
    }

    public function test_name_is_required(): void
    {
        $bank = Bank::factory()->create();
        $brand = BrandCard::factory()->create();

        Livewire::test(ListCards::class)
            ->callTableAction('createCard', data: [
                'bank_id' => $bank->id,
                'name' => '',
                'number' => '1234 5678 9012 3456',
                'brand_id' => $brand->id,
                'due_date' => 10,
                'limit' => '1500,00',
            ])
            ->assertHasErrors(['mountedTableActionsData.0.name' => 'required']);

    }

    public function test_bank_id_is_required(): void
    {
        $brand = BrandCard::factory()->create();

        Livewire::test(ListCards::class)
            ->callTableAction('createCard', data: [
                'bank_id' => null,
                'name' => 'Cartão Teste',
                'number' => '1234 5678 9012 3456',
                'brand_id' => $brand->id,
                'due_date' => 10,
                'limit' => '1500,00',
            ])
            ->assertHasErrors(['mountedTableActionsData.0.bank_id' => 'required']);
    }

    public function test_due_date_is_required(): void
    {
        $bank = Bank::factory()->create();
        $brand = BrandCard::factory()->create();

        Livewire::test(ListCards::class)
            ->callTableAction('createCard', data: [
                'bank_id' => $bank->id,
                'name' => 'Cartão Teste',
                'number' => '1234 5678 9012 3456',
                'brand_id' => $brand->id,
                'due_date' => null,
                'limit' => '1500,00',
            ])
            ->assertHasErrors(['mountedTableActionsData.0.due_date' => 'required']);
    }

    public function test_limit_is_required(): void
    {
        $bank = Bank::factory()->create();
        $brand = BrandCard::factory()->create();

        Livewire::test(ListCards::class)
            ->callTableAction('createCard', data: [
                'bank_id' => $bank->id,
                'name' => 'Cartão Teste',
                'number' => '1234 5678 9012 3456',
                'brand_id' => $brand->id,
                'due_date' => 10,
                'limit' => null,
            ])
            ->assertHasErrors(['mountedTableActionsData.0.limit' => 'required']);
    }
}
