<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $headers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $token = $this->user->createToken('TestToken')->plainTextToken;

        $this->headers = [
            'Authorization' => "Bearer $token",
            'Accept' => 'application/json',
        ];
    }

    public function test_user_can_list_their_transactions()
    {
        Transaction::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/transactions', $this->headers);

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_user_can_create_transaction()
    {
        $category = Category::factory()->create();

        $data = [
            'category_id' => $category->id,
            'amount' => 500.00,
            'description' => 'Salary',
            'date' => now()->toDateString(),
        ];

        $response = $this->postJson('/api/v1/transactions', $data, $this->headers);

        $response->assertCreated()
            ->assertJsonFragment(['amount' => 500.00]);
        $this->assertDatabaseHas('transactions', ['description' => 'Salary']);
    }

    public function test_user_can_view_specific_transaction()
    {
        $transaction = Transaction::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/v1/transactions/{$transaction->id}", $this->headers);

        $response->assertOk()
            ->assertJsonFragment(['id' => $transaction->id]);
    }

    public function test_user_can_update_transaction()
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 200,
        ]);

        $updatedData = ['amount' => 300];

        $response = $this->putJson("/api/v1/transactions/{$transaction->id}", $updatedData, $this->headers);

        $response->assertOk()
            ->assertJsonFragment(['amount' => 300]);
    }

    public function test_user_can_delete_transaction()
    {
        $transaction = Transaction::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/v1/transactions/{$transaction->id}", [], $this->headers);

        $response->assertNoContent();
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    public function test_user_cannot_access_others_transactions()
    {
        $otherUser = User::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson("/api/v1/transactions/{$transaction->id}", $this->headers);

        $response->assertStatus(404); // No debería encontrarse la transacción de otro usuario
    }
}
