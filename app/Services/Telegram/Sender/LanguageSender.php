<?php

namespace App\Services\Telegram\Sender;

use App\Models\User;
use App\Telegram\BotInstance;
use Illuminate\Support\Facades\App;
use Telegram\Bot\Actions;
use Telegram\Bot\Keyboard\Keyboard;

class LanguageSender
{
    private $bot;

    public function __construct(
        BotInstance $bot
    )
    {
        $this->bot = $bot->getBot();
    }
    public function sendRequstOnLanguage($chatId){


        $user = User::getUser($chatId);
        if($user!=[])
        App::setLocale($user["lang"]);


        $languages = [
            ["English"],
            ["Ukrainian"],
            ["Russian"]
        ];


        $this->bot->sendChatAction(["chat_id"=>$chatId,'action' => "typing"]);

        $reply_markup = Keyboard::button([
            'keyboard' => $languages,
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
            "selective" => false,
            "remove_keyboard" => true


        ]);

        $response = $this->bot->sendMessage([
            'chat_id' => $chatId,
            'text' => __("message.language"),
            'reply_markup' => $reply_markup
        ]);

    }

}
