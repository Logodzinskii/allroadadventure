<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
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

        $lid = explode('.', $this->callbackData);
        switch ($lid[0]) {
            case 'event':
                $bot->sendMessage($this->chatId, $lid[1]);
                break;
        }
    }

}
