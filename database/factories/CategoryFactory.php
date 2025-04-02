<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Definir el modelo de estado predeterminado.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word, // Nombre de la categoría
            'type' => $this->faker->randomElement(['income', 'expense']), // Tipo de la categoría ('income' o 'expense')
        ];
    }
}
