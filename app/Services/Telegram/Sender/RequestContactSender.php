<?php

namespace App\Services\Telegram\Sender;

use App\Models\User;
use App\Telegram\BotInstance;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Keyboard\Button;
use Telegram\Bot\Keyboard\Keyboard;

class RequestContactSender
{
    private $bot;

    public function __construct(
        BotInstance $bot
    )
    {
        $this->bot = $bot->getBot();
    }

    public function sendRequestContact($chatId)
    {
        $user=User::getUser($chatId);
        App::setLocale($user["lang"]);
        $button = Button::make([
            "text" => "share contact",
            "request_contact" => true
        ]);
        $keyboard = [
            [$button]
        ];

        $reply_markup = Keyboard::button([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
            "selective" => false

        ]);

        return $this->bot->sendMessage([
            'chat_id' => $chatId,
            'text' => __("message.contact"),
            'reply_markup' => $reply_markup
        ]);
    }


}
