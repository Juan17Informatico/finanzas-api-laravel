<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test para verificar que un usuario autenticado puede obtener todas las categorías.
     */
    public function test_index_returns_all_categories()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    /**
     * Test para verificar que un usuario autenticado puede crear una categoría válida.
     */
    public function test_store_creates_new_category()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = [
            'name' => 'Salario',
            'type' => 'income',
        ];

        $response = $this->postJson('/api/v1/categories', $data);

        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'name', 'type', 'created_at', 'updated_at']);

        $this->assertDatabaseHas('categories', $data);
    }

    /**
     * Test para verificar que no se puede crear una categoría con nombre duplicado.
     */
    public function test_store_fails_with_duplicate_name()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Category::factory()->create(['name' => 'Transporte']);

        $response = $this->postJson('/api/v1/categories', [
            'name' => 'Transporte',
            'type' => 'expense',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('name');
    }

    /**
     * Test para mostrar una categoría específica.
     */
    public function test_show_returns_category()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create();

        $response = $this->getJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $category->id,
                     'name' => $category->name,
                     'type' => $category->type,
                 ]);
    }

    /**
     * Test para verificar que no se encuentra una categoría inexistente.
     */
    public function test_show_returns_404_if_not_found()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/categories/999");

        $response->assertStatus(404);
    }

    /**
     * Test para actualizar correctamente una categoría.
     */
    public function test_update_updates_category()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create([
            'name' => 'Comida',
            'type' => 'expense',
        ]);

        $response = $this->putJson("/api/v1/categories/{$category->id}", [
            'name' => 'Alimentación',
            'type' => 'expense',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['name' => 'Alimentación']);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Alimentación',
        ]);
    }

    /**
     * Test para verificar que no se puede actualizar a un nombre ya existente.
     */
    public function test_update_fails_with_duplicate_name()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $existing = Category::factory()->create(['name' => 'Renta']);
        $category = Category::factory()->create(['name' => 'Servicios']);

        $response = $this->putJson("/api/v1/categories/{$category->id}", [
            'name' => 'Renta',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('name');
    }

    /**
     * Test para eliminar una categoría existente.
     */
    public function test_destroy_deletes_category()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /**
     * Test para intentar eliminar una categoría inexistente.
     */
    public function test_destroy_returns_404_if_not_found()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/categories/999");

        $response->assertStatus(404);
    }
}