<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $features = [
            'title' => 'Наши преимущества',
            'items' => ['Быстрая доставка', 'Гарантия качества', 'Низкие цены']
        ];
        $title = 'Это стандартный Blade-шаблон';
        return view('pages.index', compact('features', 'title'));
    }
}
