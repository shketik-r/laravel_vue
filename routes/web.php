<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\QrController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Главная страница
Route::get('/', [HomeController::class, 'index'])->name('home');

// Страница "О нас"
Route::get('about', [AboutController::class, 'index'])->name('about.index');

// QR
Route::get('qr', [QrController::class, 'index'])->name('qr.index');

// API
Route::get('/api/form-fields', [FormController::class, 'getFields']);
Route::post('/api/form-submit', [FormController::class, 'submit']);
