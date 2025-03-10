<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type', // 'income' o 'expense'
    ];

    // Relación: Una categoría puede tener muchas transacciones
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
