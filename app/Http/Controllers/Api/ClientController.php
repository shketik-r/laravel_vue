<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\NoReturn;

// Подключаем модель для работы с базой

class ClientController extends Controller
{
    public function store(Request $request): \Illuminate\Http\JsonResponse
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

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        // 1. По умолчанию сортируем по id, но если пришел age — берем его
        $sortBy = $request->get('sort_by', 'created_at');

        // 2. Направление сортировки: asc (по возрастанию) или desc (по убыванию)
        $sortOrder = $request->get('sort_order', 'desc');

        // Безопасная проверка, чтобы хакеры не подставили левые колонки в SQL
        if (!in_array($sortBy, ['name', 'age', 'created_at'])) {
            $sortBy = 'created_at';
        }
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        // 3. Делаем запрос к базе с учетом выбранной сортировки
        $clients = Client::orderBy($sortBy, $sortOrder)->paginate(3);

        return response()->json($clients, 200);
    }


    public function update(Request $request, $id)
    {
        // 1. Находим клиента в MySQL по ID или отдаем ошибку 404
        $client = Client::findOrFail($id);

        // 2. Валидируем пришедшие измененные данные
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:1',
        ]);

        // 3. Обновляем запись в базе данных одной строчкой через Eloquent ORM
        $client->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Данные клиента успешно изменены!',
            'data' => $client
        ], 200);
    }


    public function delete($id): \Illuminate\Http\JsonResponse
    {
        $client = Client::findOrFail($id);

        // 2. Магия Eloquent ORM: удаляем строку из таблицы
        $client->delete();

        // 3. Отдаем Vue-компоненту JSON-ответ об успехе
        return response()->json([
            'success' => true,
            'message' => 'Клиент успешно удален из системы!'
        ], 200);
    }


}
