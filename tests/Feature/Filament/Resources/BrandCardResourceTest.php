<?php

namespace Feature\Filament\Resources;

use App\Filament\Resources\BrandCardResource;
use App\Filament\Resources\BrandCardResource\Pages\CreateBrandCard;
use App\Filament\Resources\BrandCardResource\Pages\ListBrandCards;
use App\Models\BrandCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Livewire\Livewire;

class BrandCardResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_render_brand_card_create_page(): void
    {
        $this->actingAs($this->user)
            ->get(BrandCardResource::getUrl('create'))
            ->assertSuccessful();
    }

    public function test_brand_card_name_is_required(): void
    {
        Livewire::test(ListBrandCards::class)
            ->callTableAction('createBrand', data: [
                'name' => '', // ← campo obrigatório vazio
            ])
            ->assertHasTableActionErrors(['name' => 'required']);
    }

    public function test_slug_is_generated_from_name(): void
    {
        Livewire::test(ListBrandCards::class)
            ->callTableAction('createBrand', data: [
                'name' => 'Meu Cartão Exemplo',
                // Não enviar o slug manualmente!
            ]);

        $this->assertDatabaseHas('brand_cards', [
            'name' => 'Meu Cartão Exemplo',
            'slug' => 'meu-cartao-exemplo',
        ]);
    }


    public function test_brand_card_can_be_created(): void
    {
        Livewire::test(ListBrandCards::class)
            ->callTableAction('createBrand', data: [
                'name' => 'Mastercard',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('brand_cards', ['name' => 'Mastercard']);
    }


    public function test_brand_card_name_must_be_unique(): void
    {
        BrandCard::factory()->create(['name' => 'Visa']);

        Livewire::test(ListBrandCards::class)
            ->callTableAction('createBrand', data: [
                'name' => 'Visa',
            ])
            ->assertHasTableActionErrors(['name' => 'unique']);
    }


    public function test_can_create_brand_card_with_image(): void
    {
        $user = User::factory()->create();

        // Criar a pasta real usada no disco configurado
        $uploadPath = public_path('uploads/brand_card');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $fakeImage = UploadedFile::fake()->image('logo.jpg');

        Livewire::test(ListBrandCards::class)
            ->callTableAction('createBrand', data: [
                'name' => 'Marca Teste',
                'slug' => 'marca-teste',
                'brand' => $fakeImage,
            ])
            ->assertHasNoErrors();

        // Verifica se o arquivo foi movido para a pasta real
        $brandCard = \App\Models\BrandCard::latest()->first();

        $this->assertNotNull($brandCard->brand);
        $this->assertFileExists(public_path('uploads/' . $brandCard->brand));
    }
}
