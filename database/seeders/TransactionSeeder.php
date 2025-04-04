<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Transaction::create([
            'user_id' => 1,
            'category_id' => 1,
            'amount' => 2500.00,
            'description' => 'Salario mensual',
            'date' => now(),
        ]);

        Transaction::create([
            'user_id' => 1,
            'category_id' => 2,
            'amount' => 500.00,
            'description' => 'Pago de alquiler',
            'date' => now(),
        ]);
    }
}
