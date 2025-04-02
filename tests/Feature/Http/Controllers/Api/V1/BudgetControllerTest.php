<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BudgetControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test para verificar que un usuario autenticado puede obtener sus presupuestos.
     *
     * @return void
     */
    public function test_index_returns_paginated_budgets()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Crear algunos presupuestos
        Budget::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/v1/budgets');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'category_id', 'limit_amount', 'user_id', 'created_at', 'updated_at'
                    ]
                ]
            ]);
    }

    /**
     * Test para verificar que un usuario autenticado puede crear un presupuesto.
     *
     * @return void
     */
    public function test_store_creates_new_budget()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create();

        $budgetData = [
            'category_id' => $category->id,
            'limit_amount' => 500.00,
        ];

        $response = $this->postJson('/api/v1/budgets', $budgetData);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'category_id', 'limit_amount', 'user_id']);
        
        // Verificar en la base de datos
        $this->assertDatabaseHas('budgets', [
            'category_id' => $category->id,
            'limit_amount' => 500.00,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test para verificar que no se puede crear un presupuesto con categoría duplicada.
     *
     * @return void
     */
    public function test_store_fails_with_duplicate_category()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create();

        // Crear un presupuesto para esa categoría
        Budget::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'limit_amount' => 500.00,
        ]);

        // Intentar crear otro presupuesto con la misma categoría
        $budgetData = [
            'category_id' => $category->id,
            'limit_amount' => 600.00,
        ];

        $response = $this->postJson('/api/v1/budgets', $budgetData);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Ya existe un presupuesto para esta categoría']);
    }

    /**
     * Test para verificar que se puede obtener un presupuesto específico.
     *
     * @return void
     */
    public function test_show_returns_single_budget()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $budget = Budget::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/v1/budgets/{$budget->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'category_id', 'limit_amount', 'user_id']);
    }

    /**
     * Test para verificar que no se puede obtener un presupuesto de otro usuario.
     *
     * @return void
     */
    public function test_show_returns_404_for_other_user_budget()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $otherUserBudget = Budget::factory()->create(); // Otro usuario

        $response = $this->getJson("/api/v1/budgets/{$otherUserBudget->id}");

        $response->assertStatus(404);
    }

    /**
     * Test para verificar que un presupuesto se puede actualizar.
     *
     * @return void
     */
    public function test_update_updates_existing_budget()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create();
        $budget = Budget::factory()->create(['user_id' => $user->id, 'category_id' => $category->id]);

        $updatedData = [
            'category_id' => $category->id,
            'limit_amount' => 800.00,
        ];

        $response = $this->putJson("/api/v1/budgets/{$budget->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson(['limit_amount' => 800.00]);

        // Verificar en la base de datos
        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'limit_amount' => 800.00,
        ]);
    }

    /**
     * Test para verificar que no se puede actualizar un presupuesto a una categoría duplicada.
     *
     * @return void
     */
    public function test_update_fails_with_duplicate_category()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        // Crear dos presupuestos
        Budget::create(['user_id' => $user->id, 'category_id' => $category1->id, 'limit_amount' => 500.00]);
        $budgetToUpdate = Budget::create(['user_id' => $user->id, 'category_id' => $category2->id, 'limit_amount' => 300.00]);

        // Intentar actualizar el presupuesto a la misma categoría
        $response = $this->putJson("/api/v1/budgets/{$budgetToUpdate->id}", [
            'category_id' => $category1->id,
            'limit_amount' => 400.00,
        ]);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Ya tienes un presupuesto para esta categoría']);
    }

    /**
     * Test para verificar que un presupuesto se puede eliminar.
     *
     * @return void
     */
    public function test_destroy_deletes_budget()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $budget = Budget::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/v1/budgets/{$budget->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
    }

    /**
     * Test para verificar la generación de un reporte de presupuestos.
     *
     * @return void
     */
    public function test_reports_returns_budget_report()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Crear algunos presupuestos
        Budget::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/v1/budgets/reports');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_budget', 'average_budget', 'max_budget', 'min_budget', 'budgets_by_category'
            ]);
    }
}
