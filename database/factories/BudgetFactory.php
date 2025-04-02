<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    /**
     * Definir el modelo de estado predeterminado.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(), // Crear un usuario aleatorio con el factory de User
            'category_id' => Category::factory(), // Crear una categoría aleatoria
            'limit_amount' => $this->faker->randomFloat(2, 10, 1000), // Limite entre 10 y 1000
        ];
    }
}
