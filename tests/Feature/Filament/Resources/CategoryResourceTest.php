<?php

namespace Feature\Filament\Resources;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use function Pest\Livewire\livewire;
use Livewire\Livewire;

class CategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_can_render_category_list_page(): void
    {
        $user = User::factory()->create();
        Category::factory()->count(3)->create();

        Livewire::actingAs($user)
            ->test(ListCategories::class)
            ->assertStatus(200)
            ->assertSee('Categorias'); // Ajuste conforme o texto da sua interface
    }

    public function test_can_create_category(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateCategory::class)
            ->fillForm([
                'name' => 'Nova Categoria',
            ])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'name' => 'Nova Categoria',
        ]);
    }

    public function test_can_edit_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['name' => 'Antigo Nome']);

        Livewire::actingAs($user)
            ->test(EditCategory::class, ['record' => $category->getKey()])
            ->fillForm([
                'name' => 'Novo Nome',
            ])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Novo Nome',
        ]);
    }

    public function test_can_delete_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Livewire::actingAs($user)
            ->test(ListCategories::class)
            ->callTableAction('delete', $category);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_validation_fails_when_name_is_missing(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateCategory::class)
            ->fillForm([
                'name' => '', // campo obrigatório vazio
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);

        $this->assertDatabaseCount('categories', 0);
    }

    public function test_validation_fails_on_edit_when_name_is_empty(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Livewire::actingAs($user)
            ->test(EditCategory::class, ['record' => $category->getKey()])
            ->fillForm([
                'name' => '',
            ])
            ->call('save')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_validation_fails_when_category_name_is_duplicated(): void
    {
        $user = User::factory()->create();

        $existingCategory = Category::factory()->create(['name' => 'Duplicado']);

        Livewire::actingAs($user)
            ->test(CreateCategory::class)
            ->fillForm([
                'name' => 'Duplicado',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'unique']);

        // Verifica que só há uma categoria no banco
        $this->assertDatabaseCount('categories', 1);
    }
}
