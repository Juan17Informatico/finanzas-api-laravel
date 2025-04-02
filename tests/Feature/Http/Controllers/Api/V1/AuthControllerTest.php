<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test para verificar que se puede registrar un usuario.
     *
     * @return void
     */
    public function test_register_creates_new_user_and_returns_token()
    {
        $userData = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure(['token']);

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);
    }

    /**
     * Test para verificar que no se puede registrar un usuario con un email duplicado.
     *
     * @return void
     */
    public function test_register_fails_with_duplicate_email()
    {
        // Crear un usuario existente
        $existingUser = User::factory()->create();

        $userData = [
            'name' => $this->faker->name(),
            'email' => $existingUser->email, // Email duplicado
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test para verificar que no se puede registrar un usuario con contraseñas no coincidentes.
     *
     * @return void
     */
    public function test_register_fails_with_password_mismatch()
    {
        $userData = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password123',
            'password_confirmation' => 'differentpassword', // Contraseña diferente
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test para verificar que se puede iniciar sesión con credenciales correctas.
     *
     * @return void
     */
    public function test_login_returns_token_with_valid_credentials()
    {
        // Crear un usuario con contraseña conocida
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure(['token']);
    }

    /**
     * Test para verificar que no se puede iniciar sesión con credenciales incorrectas.
     *
     * @return void
     */
    public function test_login_fails_with_invalid_credentials()
    {
        // Crear un usuario con contraseña conocida
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Intentar con contraseña incorrecta
        $loginData = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test para verificar que se puede cerrar sesión.
     *
     * @return void
     */
    public function test_logout_deletes_user_tokens()
    {
        $user = User::factory()->create();
        
        // Autenticar al usuario usando Sanctum
        Sanctum::actingAs($user);
        
        // Hacer la petición de logout estando autenticado
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Sesión cerrada']);

        // Verificar que el token fue eliminado
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    /**
     * Test para verificar que se puede obtener la información del usuario autenticado.
     *
     * @return void
     */
    public function test_me_returns_authenticated_user_info()
    {
        $user = User::factory()->create();
        
        // Autenticar al usuario usando Sanctum
        Sanctum::actingAs($user);

        // Hacer la petición para obtener la información del usuario
        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }

    /**
     * Test para verificar que no se puede acceder a la información del usuario sin estar autenticado.
     *
     * @return void
     */
    public function test_me_returns_401_for_unauthenticated_user()
    {
        // Hacer la petición sin autenticación
        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(401);
    }
    
    /**
     * Test para verificar que no se puede cerrar sesión sin estar autenticado.
     *
     * @return void
     */
    public function test_logout_returns_401_for_unauthenticated_user()
    {
        // Hacer la petición sin autenticación
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(401);
    }
}