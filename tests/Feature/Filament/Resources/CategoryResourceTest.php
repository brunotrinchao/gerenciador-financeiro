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
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_render_category_list_page(): void
    {
        $user = User::factory()->create();
        Category::factory()->count(3)->create();

        Livewire::test(ListCategories::class)
            ->assertStatus(200)
            ->assertSee('Categorias'); // Ajuste conforme o texto da sua interface
    }

    public function test_can_create_category(): void
    {

        Livewire::test(ListCategories::class)
            ->callTableAction('createCategory', data: [
                'name' => 'Nova Categoria',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('categories', [
            'name' => 'Nova Categoria',
        ]);
    }

    public function test_can_edit_category(): void
    {
        $category = Category::factory()->create(['name' => 'Antigo Nome']);

        Livewire::test(ListCategories::class)
            ->callTableAction('editCategory', $category, data: [
                'name' => 'Novo Nome',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Novo Nome',
        ]);
    }

    public function test_can_delete_category(): void
    {
        $category = Category::factory()->create();

        Livewire::test(ListCategories::class)
            ->callTableAction('delete', $category);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_validation_fails_when_name_is_missing(): void
    {
        Livewire::test(ListCategories::class)
            ->callTableAction('createCategory', data: [
                'name' => '', // campo obrigatório vazio
            ])
            ->assertHasTableActionErrors(['name' => 'required']);

        $this->assertDatabaseCount('categories', 0);
    }

    public function test_validation_fails_on_edit_when_name_is_empty(): void
    {
        $category = Category::factory()->create();

        Livewire::test(ListCategories::class)
            ->callTableAction('editCategory', $category, data: [
                'name' => '',
            ])
            ->assertHasTableActionErrors(['name' => 'required']);
    }

    public function test_validation_fails_when_category_name_is_duplicated(): void
    {

        Category::factory()->create(['name' => 'Duplicado']);

        $response=Livewire::test(ListCategories::class)
            ->callTableAction('createCategory', data: [
                'name' => 'Duplicado',
            ])
        ->assertHasTableActionErrors(['name' => 'unique']);

        // Verifica que só há uma categoria no banco
        $this->assertDatabaseCount('categories', 1);
    }
}
