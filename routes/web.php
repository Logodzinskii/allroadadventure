<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
/**
 * TelegramBot
 */
Route::post('send/bot/message/',[\App\Http\Controllers\Telegram\telegramController::class, 'SendMessage'])->name('send.message.bot');
Route::any('telegramsecret', [\App\Http\Controllers\Telegram\telegramController::class, 'getMess']);
