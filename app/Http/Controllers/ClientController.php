<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client; // Подключаем модель для работы с базой

class ClientController extends Controller
{
    public function store(Request $request)
    {
        // 1. Валидация пришедших от Vue данных
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:1',
        ]);

        // 2. Создание записи в таблице `clients` через ORM Eloquent
        $client = Client::create($validated);

        // 3. Отдаем Vue-компоненту красивый JSON-ответ
        return response()->json([
            'success' => true,
            'message' => 'Клиент успешно сохранен в базу данных!',
            'data' => $client
        ], 201);
    }
}
