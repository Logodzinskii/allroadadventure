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
                    $this->message->sendMessage('/events - информация о фестивалях');
                    break;
                case '/events':
                    /**
                     * получаем названия фестивалей и сроки проведения, генерим массив кнопок
                     *
                     */
                    $button = [];
                    for($i=0; $i<=6; $i++){
                        $button[]=["text" => "Абунафест, 2-4 июня, Аргамач".$i, "callback_data" => 'catalog.kitchen'];
                    }
                    $button[]=["text" => "Сообщить о фестивале", "callback_data" => 'catalog.kitchen'];
                    $this->message->sendKeyboard('Выбрать', array("inline_keyboard" => array_chunk($button,1)));
                    break;
                case '/addEvents':
                    /**
                     * Начинаем диалог по добавлению События в базу данных
                     */
                    $this->message->sendMessage('Пришлите мне название мероприятия, обязательно начните сообщение со слова - событие Например: событие - Абунафест');

                    break;
                default:
                    if(preg_grep('/^(событие)/', explode("\n", $data['text'])))
                    {
                        /**
                         * Создадим сессию
                         */
                        session_start();
                        $_SESSION['name'] = $data['text'];
                        $button =[];
                        $button[]=["text" => "Сохранить", "callback_data" => 'catalog.kitchen'];
                        $button[]=["text" => "Отмена", "callback_data" => 'catalog.kitchen'];
                        $this->message->sendKeyboard('Для продолжения выберите', array("inline_keyboard" => array_chunk($button,2)));
                        $this->message->sendMessage('Сообщите мне дату начала '. $_SESSION['name'] .'. Например: Дата начала 23.12.2023');
                    }elseif (preg_match('/(^Дата начала )(\d\d)[.](\d\d)[.](\d\d\d\d)/', $data['text'], $output_array))
                    {
                        session_start();
                        $this->message->sendMessage($output_array[0].' установлена для '. $_SESSION['name'] . '.');
                        $this->message->sendMessage('Сообщите мне дату завершения события. Например: Дата завершения 23.12.2023');
                    }elseif (preg_match('/(^Дата завершения )(\d\d)[.](\d\d)[.](\d\d\d\d)/', $data['text'], $output_array))
                    {
                        session_start();
                        $this->message->sendMessage($output_array[0] . ' установлена для '. $_SESSION['name'] . '.');
                        $this->message->sendMessage('Сообщите мне координаты северной широты, восточной долготы. Например: 56.815348, 60.665473. ');
                    }elseif (preg_match_all('/-?\d{1,3}\.\d+/', $data['text'], $output_array))
                    {
                        session_start();
                        $this->message->sendMessage('Координаты установлены '. $output_array[0][0] . ', ' .$output_array[0][1] );
                        $this->message->sendMessage('Картинка ');
                    }else{
                        $this->message->sendMessage('я не понял');
                    }
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
