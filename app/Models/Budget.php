<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'limit_amount',
    ];

    // Relación: Un presupuesto pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación: Un presupuesto pertenece a una categoría
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
