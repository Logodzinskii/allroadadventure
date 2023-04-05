<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Telegram\UserController;
use App\Http\Controllers\Telegram\MessageController;

class telegramController extends Controller
{

    /**
     * Методы взаимодействия с сайтом из ботаoy
     */
    public function getMess()
    {
        /**
         * Получим данные от телеграм
         * @param $data array
         */
        $content = file_get_contents("php://input");
        $data = json_decode($content, true);

        $data = new UpdateDateController($data['message']);

    }

    public function getTelegramData()
    {
        $bot = new \TelegramBot\Api\BotApi(config('conftelegram.telegram.token'));


        $content = file_get_contents("php://input");
        $data = json_decode($content, true);

        if (isset($data['callback_query'])) {

            $data = $data['callback_query'];
            $chat_id = $data['message']['chat']['id'];

            $lid = explode('.', $data['data']);
            switch ($lid[0]) {
                case 'lid':
                    if ($chat_id == config('conftelegram.telegram.admin') || $chat_id == config('conftelegram.telegram.manager'))
                    {
                        $order = DB::table('orders')
                            ->where('status', $lid[1])->get();
                        foreach ($order as $lid) {
                            $name = $lid->name;
                            $email = $lid->userEmail;
                            $id = $lid->id;
                            $inline_button1 = ["text" => "Новый", "callback_data" => 'update.new.' . $id];
                            $inline_button2 = ["text" => "В работе", "callback_data" => 'update.work.' . $id];
                            $inline_button3 = ["text" => "Завершен", "callback_data" => 'update.success.' . $id];
                            $inline_button4 = ["text" => "Отказ", "callback_data" => 'update.denied.' . $id];
                            $inline_keyboard = [[$inline_button1, $inline_button2],
                                [$inline_button3, $inline_button4],];
                            $keyboard = array("inline_keyboard" => $inline_keyboard);

                            $this->sendKeyboard($chat_id, $name . $email . ' Изменить статус', $keyboard);
                        }
                    }else{
                        $bot->sendMessage($chat_id, 'access denied');
                        die();
                    }
                    break;
                case 'update':
                    if ($chat_id == config('conftelegram.telegram.admin') || $chat_id == config('conftelegram.telegram.manager'))
                    {
                        DB::table('orders')
                            ->where('id',$lid[2])
                            ->update(['status'=>$lid[1]]);
                        $bot->sendMessage($chat_id, 'Статус лида ' . $lid[2] .' изменен на: ' . $lid[1]);
                    }else{
                        $bot->sendMessage($chat_id, 'access denied');
                        die();
                    }
                    break;
                case 'user':
                    if ($chat_id == config('conftelegram.telegram.admin') || $chat_id == config('conftelegram.telegram.manager'))
                    {
                        $users = DB::table('users')
                            ->where('status', $lid[1])->get();
                        foreach ($users as $user) {
                            $name = $user->name;
                            $email = $user->email;
                            $id = $user->id;
                            $inline_button1 = ["text" => "Админ", "callback_data" => 'updateUser.admin.' . $id];
                            $inline_button2 = ["text" => "Гость", "callback_data" => 'updateUser.users.' . $id];

                            $inline_keyboard = [[$inline_button1, $inline_button2]];
                            $keyboard = array("inline_keyboard" => $inline_keyboard);

                            $this->sendKeyboard($chat_id,$name . $email . ' Изменить статус' , $keyboard);
                        }
                    }else{
                        $bot->sendMessage($chat_id, 'access denied');
                        die();
                    }
                    break;
                case 'project':
                    $projects = DB::table('complete_projects')
                        ->where('category', $lid[1])->get();
                    foreach ($projects as $project) {
                        $title = $project->meta_title;
                        $descriptions = $project->meta_descriptions;
                        $id = $project->id;
                        $link = 'https://kitchenural.ru/complete/' . $project->chpu_complite;

                        $response = array(
                            'chat_id' => $data['message']['chat']['id'],
                            'photo' => curl_file_create(json_decode($project->image,true)[0]),
                            'caption' => $title . ' ' . $descriptions . ' ' . $link,
                        );

                        $this->sendPhoto($response);
                        if ($chat_id == config('conftelegram.telegram.admin') || $chat_id == config('conftelegram.telegram.manager')) {
                            $inline_button1 = ["text" => "В архив", "callback_data" => 'updateProject.archive.' . $id];
                            $inline_button2 = ["text" => "На витрину", "callback_data" => 'updateProject.visible.' . $id];

                            $inline_keyboard = [[$inline_button1, $inline_button2]];
                            $keyboard = array("inline_keyboard" => $inline_keyboard);

                            $this->sendKeyboard($chat_id, 'готовый проект' , $keyboard);
                        }

                    }
                    break;
                case "catalog":
                    $catalogs = DB::table('catalogs')
                        ->get();
                    foreach ($catalogs as $item) {
                        $title = $item->type;
                        $descriptions = $item->meta_descriptions;
                        $id = $item->id;
                        $price = $item->price;
                        $link = 'https://kitchenural.ru/catalog/' . $item->chpu;

                        $response = array(
                            'chat_id' => $data['message']['chat']['id'],
                            'photo' => curl_file_create(json_decode($item->image,true)[0]),
                            'caption' => '🏷' . $title . '. ' . $descriptions . '💳 '. $price .', ' . '🔗 '. $link,
                        );

                        $this->sendPhoto($response);
                        if ($chat_id == config('conftelegram.telegram.admin') || $chat_id == config('conftelegram.telegram.manager')) {
                            $inline_button1 = ["text" => "В архив", "callback_data" => 'updateProject.archive.' . $id];
                            $inline_button2 = ["text" => "На витрину", "callback_data" => 'updateProject.visible.' . $id];

                            $inline_keyboard = [[$inline_button1, $inline_button2]];
                            $keyboard = array("inline_keyboard" => $inline_keyboard);

                            $this->sendKeyboard($chat_id, 'готовый проект' , $keyboard);
                        }

                    }
                    break;
            }


        } elseif (isset($data['message'])) {
            $data = $data['message'];
            $message = mb_strtolower($data['text']);

            /**
             * Если получили команду
             */

            switch ($message) {
                case '/start':
                    if ($data['chat']['id'] == config('conftelegram.telegram.admin') ||$data['chat']['id'] == config('conftelegram.telegram.manager')) {
                        $inline_button1 = ["text" => "лиды"];
                        $inline_button2 = ["text" => "пользователи"];
                        $inline_button3 = ["text" => "проекты"];
                        $inline_button4 = ["text" => "типовые проекты"];
                        $inline_keyboard = [[$inline_button1, $inline_button2],
                            [$inline_button3, $inline_button4]];
                    }else{

                        $inline_button1 = ["text" => "проекты"];
                        $inline_button2 = ["text" => "типовые проекты"];
                        $inline_keyboard = [[$inline_button1 , $inline_button2]];
                    }

                    //$keyboard = array("inline_keyboard" => $inline_keyboard);

                    $keyboard = [
                        "keyboard" => $inline_keyboard,
                        'one_time_keyboard' => false,
                        'resize_keyboard' => true,

                    ];
                    $this->sendKeyboard($data['chat']['id'],'Выберите статус', $keyboard);

                    break;
                case "лиды":
                    $inline_button1 = ["text" => "Новый лид", "callback_data" => 'lid.new'];
                    $inline_button2 = ["text" => "Лид в работе", "callback_data" => 'lid.work'];
                    $inline_button3 = ["text" => "Завершен", "callback_data" => 'lid.success'];
                    $inline_button4 = ["text" => "Отказ", "callback_data" => 'lid.denied'];
                    $inline_keyboard = [[$inline_button1, $inline_button2],
                        [$inline_button3, $inline_button4],];
                    $keyboard = array("inline_keyboard" => $inline_keyboard);
                    $this->sendKeyboard($data['chat']['id'],'Выберите статус', $keyboard);
                    break;
                case "пользователи":
                    $inline_button1 = ["text" => "админ", "callback_data" => 'user.admin'];
                    $inline_button2 = ["text" => "гость", "callback_data" => 'user.users'];

                    $inline_keyboard = [[$inline_button1, $inline_button2]];
                    $keyboard = array("inline_keyboard" => $inline_keyboard);
                    $this->sendKeyboard($data['chat']['id'],'Выберите статус', $keyboard);
                    break;
                case "проекты":
                    $inline_button1 = ["text" => "кухни", "callback_data" => 'project.kitchen'];
                    $inline_button2 = ["text" => "шкафы", "callback_data" => 'project.wardrobe'];
                    $inline_button3 = ["text" => "в ванную", "callback_data" => 'project.bathroom'];
                    $inline_keyboard = [[$inline_button1, $inline_button2],[$inline_button3]];
                    $keyboard = array("inline_keyboard" => $inline_keyboard);
                    $this->sendKeyboard($data['chat']['id'],'Выберите тип проектов', $keyboard);
                    break;
                case "типовые проекты":
                    $inline_button1 = ["text" => "кухни", "callback_data" => 'catalog.kitchen'];
                    $inline_keyboard = [[$inline_button1]];
                    $keyboard = array("inline_keyboard" => $inline_keyboard);
                    $this->sendKeyboard($data['chat']['id'],'Посмотреть каталог', $keyboard);
                    break;
                case "/help":
                    $bot->sendMessage($data['chat']['id'], 'hello world');//'-1001544908866'
                    break;
                default:

                    $bot->sendMessage($data['chat']['id'], 'wtf!' . json_encode($data));
            }
        } else{
            $bot->sendMessage($data['chat']['id'], 'Что это?' . json_encode($data));
        }
    }



    protected function sendKeyboard($chat_id, $text, $keyboard)
    {
        $bottoken = config('conftelegram.telegram.token');

        $reply = $text;

        $url = "https://api.telegram.org/bot".$bottoken."/sendMessage";

        $postfields = array(
            'chat_id' => $chat_id,
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

    protected function sendPhoto($response)
    {

        $token = config('conftelegram.telegram.token');
        $ch = curl_init('https://api.telegram.org/bot' . $token . '/sendPhoto');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $response);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * Метод отправки сообщения телеграм боту из файла
     * @param Request $request
     * @throws \TelegramBot\Api\Exception
     * @throws \TelegramBot\Api\InvalidArgumentException
     */
    public function SendMessage(Request $request)
    {
        $bot = new \TelegramBot\Api\BotApi(config('conftelegram.telegram.token'));
        if($_SERVER['SERVER_NAME'] !=='kitchenural.local') {
            $bot->sendMessage(config('conftelegram.telegram.admin'), $request['message']);
        }else{
            $bot->sendMessage(config('conftelegram.telegram.admin'), $request['message']);
            $bot->sendMessage(config('conftelegram.telegram.manager'), $request['message']);
        }

    }
}
