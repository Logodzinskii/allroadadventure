<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Telegram\MessageController;
use App\Http\Controllers\Telegram\UserController;
use Illuminate\Http\Request;


class UpdateDateController extends Controller
{
    public $user, $message, $callback, $photo, $file;

    public function __construct($data)
    {

        $chatType = $data['chat']['type'];
        $chatId = $data['chat']['id'];
        $chatTitle = $data['chat']['title'];
        $messageId = $data['message_id'];

        $from = $data['from'];
        $fromId = $from['id'];
        $fromFirstName = $from['first_name'];
        $fromLastName = $from['last_name'];
        $fromUserName = $from['username'];

        $this->user = new UserController($fromFirstName, $fromLastName, $fromUserName, $fromId);

        /**
         * Если простое сообщение, то передадим текст сообщения в MessageController
         */
        if(isset($data['text']))
        {
            $this->message = new MessageController($data['text'], $chatType, $chatId, $chatTitle, $messageId) ;

            switch ($data['text']){
                case '/help':
                    $this->message->sendMessage('/festival1 - информация о фестивалях');
                    break;
                case '/festival':
                    /**
                     * получаем названия фестивалей и сроки проведения, генерим массив кнопок
                     *
                     */
                    $button = [];
                    for($i=0; $i<=6; $i++){
                        $button[]=["text" => "Абунафест, 2-4 июня, Аргамач".$i, "callback_data" => 'catalog.kitchen'];
                    }

                    $this->message->sendKeyboard('Выбрать', array("inline_keyboard" => array_chunk($button,1)));
                    break;
                default:
                    $this->message->sendMessage('я не понял');
            }

        }elseif($data['callback_query'])
        {
            $this->callback = '';
        }
        else{
            $this->message = new MessageController('null', $chatType, $chatId, $chatTitle, $messageId) ;
        }

    }

    public function getTypeMessage()
    {
        print_r($this->user->getFullNameUser() . ' ' . $this->message->getMessage());
    }
}
