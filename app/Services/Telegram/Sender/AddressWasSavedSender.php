<?php

namespace App\Services\Telegram\Sender;

use App\Telegram\BotInstance;
use Illuminate\Support\Facades\App;

class AddressWasSavedSender
{

    private $bot;

    public function __construct(
        BotInstance $bot
    )
    {
        $this->bot = $bot->getBot();
    }

    public function sendMessageAboutAddressWasSaved($chatId,$lang){
        App::setLocale($lang);
        $text=__("message.address_was_saved");
        $this->bot->sendMessage([
            'chat_id' => $chatId,
            'parse_mod'=>"HTML",
            'text'=>$text,

        ]);
    }

    public function sendMessageAboutAddressWasNotSaved($chatId){

        $this->bot->sendMessage([
            'chat_id' => $chatId,
            'text'=>__("message.try_again_input_address"),

        ]);
    }
}
