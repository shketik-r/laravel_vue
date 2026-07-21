<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Импортируем правильный тип связи

class Client extends Model
{
    // Убедитесь, что 'category_id' разрешен для записи
    protected $fillable = ['name', 'age', 'category_id'];

    /**
     * Связь: Клиент принадлежит категории.
     * Имя метода должно быть строго в единственном числе - category!
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
