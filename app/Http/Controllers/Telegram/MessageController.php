<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public string   $message,
                    $chatType,
                    $chatId,
                    $chatTitle,
                    $messageId;
    public function __construct($message, $chatType, $chatId, $chatTitle, $messageId)
    {
        $this->message = $message;
        $this->chatType =$chatType;
        $this->chatId =$chatId;
        $this->chatTitle =$chatTitle;
        $this->messageId =$messageId;
    }
    public function getMessageId()
    {
        return $this->messageId;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function updateMessage()
    {

    }

    public function deleteMessage()
    {

    }

    public function sendMessage(string $responseMessage)
    {
        $bot = new \TelegramBot\Api\BotApi(config('conftelegram.telegram.token'));
        $bot->sendMessage($this->chatId, $responseMessage);
    }

    public function sendKeyboard($text, $keyboard)
    {
        $bottoken = config('conftelegram.telegram.token');

        $reply = $text;

        $url = "https://api.telegram.org/bot".$bottoken."/sendMessage";

        $postfields = array(
            'chat_id' => $this->chatId,
            'text' => $reply,
            'reply_markup' => json_encode($keyboard)
        );

        if (!$curld = curl_init()) {
            exit;
        }

        curl_setopt($curld, CURLOPT_POST, true);
        curl_setopt($curld, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($curld, CURLOPT_URL,$url);
        curl_setopt($curld, CURLOPT_RETURNTRANSFER, true);

        $output = curl_exec($curld);

        curl_close ($curld);
    }
}
