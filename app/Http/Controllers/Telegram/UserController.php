<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Telegram\telegramController;

class UserController extends Controller
{
    public string $fromFirstName, $fromLastName, $fromUserName, $fromId;
    public function __construct($fromFirstName, $fromLastName, $fromUserName, $fromId)
    {
        $this->fromFirstName = $fromFirstName;
        $this->fromLastName = $fromLastName;
        $this->fromUserName = $fromUserName;
        $this->fromId = $fromId;
    }

    public function getFullNameUser()
    {
        return $this->fromFirstName . ' ' . $this ->fromLastName;
    }

    public function getUserChatId()
    {
        return $this->fromId;
    }
    public function sendMessage()
    {

    }

}
