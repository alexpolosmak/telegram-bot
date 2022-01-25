<?php

namespace App\Services\Telegram\Sender;

use App\Models\User;
use App\Telegram\BotInstance;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class CreateOrderSender
{
    private $bot;

    public function __construct(
        BotInstance $bot
    )
    {
        $this->bot = $bot->getBot();
    }

    public function sendInviteForCreateOrder($chatId)
    {
        $this->bot->sendChatAction([
            'chat_id' => $chatId,
            "action"=>"typing"
        ]);

        $user=User::getUser($chatId);
        App::setLocale($user["lang"]);
        Cache::put("add",$chatId);
        $this->bot->sendChatAction([
            'chat_id' => $chatId,
            "action" => "typing"
        ]);
        $text=__("message.create_order");;
        return $this->bot->sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode'=>"HTML"

        ]);


    }

}
