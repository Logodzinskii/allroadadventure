<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Telegram\MessageController;
use App\Http\Controllers\Telegram\UserController;
use App\Models\Events;
use Illuminate\Http\Request;


class UpdateDateController extends Controller
{
    public $user, $message, $callback, $photo, $file;

    public function __construct($data)
    {

        $chatType = $data['chat']['type'];
        $chatId = $data['chat']['id'];
        $chatTitle = $data['chat']['title'] ?? 'private';
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
            $bot = new \TelegramBot\Api\BotApi(config('conftelegram.telegram.token'));
            switch ($data['text']){
                case '/help':
                    $bot->sendMessage($chatId,'/events - информация о фестивалях');
                    break;
                case '/events':
                    /**
                     * получаем названия фестивалей и сроки проведения, генерим массив кнопок
                     *
                     */
                    $button = [];
                    $arr = json_decode(Events::all(),true);
                    foreach ($arr as $event)
                    {
                        $button[]=["text" => $event['name'], "callback_data" => 'event|' . $event['id']];
                    }

                    $button[]=["text" => "Сообщить о фестивале", "callback_data" => 'addevent'];

                    $this->message->sendKeyboard('Выбрать', array("inline_keyboard" => array_chunk($button,1)));
                    break;
                case '/addEvents':
                    /**
                     * Начинаем диалог по добавлению События в базу данных
                     */
                    $bot->sendMessage($chatId,'событие Абунафест, сайт example.ru, начало 20.04.2023, окончание 23.04.2023, координаты 56.815348, 60.665473, организатор @ekasaitlim');

                    break;
                default:
                    if(preg_grep('/^(событие)/', explode("\n", $data['text'])))
                    {
                        $table = new Events;
                        $res = $this->parseAddEvents($data['text']);


                        if(isset($res['событие'])){
                            $table->name = $res['событие'];

                        }elseif (isset($res['сайт'])) {
                            $table->webUrl = $res['сайт'];

                        }elseif (isset($res['description'])) {
                            $table->description ='';

                        }elseif (isset($res['координаты'])) {
                            $coordinate = explode(',', $res['координаты']);
                            $table->latitude = $coordinate[0];
                            $table->longitude = $coordinate[1];
                    }else{
                            $table->name = 'error';
                        }
                        $table->save();
                        /**
                         * Добавляем данные в базу данных
                         */

                        $loc = explode(',', $res[4]);
                        $bot->sendLocation($chatId, $loc[0], $loc[1]);

                        $bot->sendMessage($chatId, $res[0] . ', '. $res[1] . ', ' . $res[2] . ', ' . $res[3] . ', ' . $res[5] . '.');

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

    protected function parseAddEvents($message)
    {
        $res = [];
        $array = [
            'событие'=>'/(событие)( )(.+?),/m',
            'сайт'=>'/(сайт)( )(.+?),/m',
            'начало'=>'/(начало)( )(.+?),/m',
            'окончание'=>'/(окончание)( )(.+?),/m',
            'координаты'=>'/-?\d{1,3}\.\d+(.+?),/m',
            'организатор'=>'/(организатор)( )(.+)/m',

        ];

        foreach ($array as $key=>$reg)
        {

            preg_match_all($reg, $message, $matches, PREG_SET_ORDER, 0);
            if(isset($matches[0])){
                if(isset($matches[0][3])){

                    $res[$key]=$matches[0][3];

                }elseif ($matches[2][0] && $reg == '/-?\d{1,3}\.\d+(.+?),/m')
                {

                    $res[$key]=$matches[2][0];

                }else{

                    $res[]='';

                }
            }

        }
        return $res;

    }
}
