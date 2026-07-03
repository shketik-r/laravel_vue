<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FormController extends Controller
{
    //  Отдает структуру полей для Vue
    public function getFields()
    {
        $fields = [
            [
                'name' => 'name',
                'label' => 'Ваше имя',
                'type' => 'text',
                'placeholder' => 'Введите имя',
                'required' => true
            ],
            [
                'name' => 'email',
                'label' => 'Email',
                'type' => 'email',
                'placeholder' => 'name@example.com',
                'required' => true
            ],
            [
                'name' => 'message',
                'label' => 'Сообщение',
                'type' => 'textarea',
                'placeholder' => 'Текст сообщения...',
                'required' => false
            ]
        ];

        return response()->json($fields);
    }

    //  Принимает данные формы
    public function submit(Request $request)
    {
        // Настраиваем строгие правила и кастомные сообщения
        $request->validate([
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email',
            'message' => 'required|string|min:5',
        ], [
            // Сообщения для поля Имя
            'name.required' => 'Поле "Ваше имя" обязательно для заполнения.',
            'name.min' => 'Имя должно содержать минимум 2 символа.',

            // Сообщения для поля Email
            'email.required' => 'Поле "Email" обязательно для заполнения.',
            'email.email' => 'Введен некорректный адрес электронной почты.',

            // Сообщения для поля Сообщение
            'message.required' => 'Поле "Сообщение" не заполнено.',
            'message.min' => 'Текст сообщения должен быть не короче 5 символов.',
        ]);

        // Если валидация прошла успешно, код идет дальше:
        return response()->json([
            'success' => true,
            'message' => 'Форма успешно отправлена и сохранена на бэкенде!'
        ]);
    }

}
