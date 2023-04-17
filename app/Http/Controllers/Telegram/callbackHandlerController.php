<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\Events;
use Illuminate\Http\Request;

class callbackHandlerController extends Controller
{

    public string $chatId;
    public string $callbackData;

    public function __construct($data)
    {
        $bot = new \TelegramBot\Api\BotApi(config('conftelegram.telegram.token'));
        $this->chatId = $data['message']['chat']['id'];
        $this->callbackData = $data['data'];

        $lid = explode('|', $this->callbackData);
        switch ($lid[0]) {
            case 'event':
                $response = Events::where('id', $lid[1])->first();

                $bot->sendLocation($this->chatId, $response['latitude'], $response['longitude']);

                $string = $response['name'] . ' начало ' . ' окончание ' . ' сайт ' . $response['webUrl'];

                $bot->sendMessage($this->chatId, $string);
                break;
            case 'addevent':
                /**
                 * Начинаем диалог по добавлению События в базу данных
                 */
                $bot->sendMessage($this->chatId,'Пришлите мне текст строго по образцу: событие Абунафест, сайт example.ru, начало 20.04.2023, окончание 23.04.2023, координаты 56.815348, 60.665473, организатор @ekasaitlim');

                break;
        }
    }

}
