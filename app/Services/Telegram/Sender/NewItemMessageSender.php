<?php

namespace App\Services\Telegram\Sender;

use App\Models\User;
use App\Services\DotsApi\RequestsAboutCities;
use App\Telegram\BotInstance;
use Illuminate\Support\Facades\App;
use Telegram\Bot\Keyboard\Keyboard;

class NewItemMessageSender
{

    private $bot;

    public function __construct(
        BotInstance $bot
    )
    {
        $this->bot = $bot->getBot();
    }

    public function sendMessageAboutNewItem($chatId, $nameItem)
    {
        $user=User::getUser($chatId);
        App::setLocale($user["lang"]);
        $this->bot->sendChatAction([
            'chat_id' => $chatId,
            "action" => "typing"
        ]);
$text=__("message.new_dish_first_item").$nameItem. __("message.new_dish_second_item");
        return $this->bot->sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode'=>"HTML"

        ]);


    }
}
