<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Salario', 'type' => 'income'],
            ['name' => 'Alquiler', 'type' => 'expense'],
            ['name' => 'Comida', 'type' => 'expense'],
            ['name' => 'Transporte', 'type' => 'expense'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
