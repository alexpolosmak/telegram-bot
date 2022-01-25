<?php

namespace App\Services\Telegram\Sender;

use App\Models\User;
use App\Services\DotsApi\RequestsAboutCities;
use App\Services\DotsApi\RequestsAboutCompanies;
use App\Telegram\BotInstance;
use Illuminate\Support\Facades\App;
use Telegram\Bot\Keyboard\Keyboard;

class GetCompanyAddressForOrder
{
    private $bot;
    private $requestAboutCompanies;

    public function __construct()
    {
        $this->requestAboutCompanies = new RequestsAboutCompanies(new RequestsAboutCities());
        $this->bot = (new BotInstance())->getBot();
    }

    public function sendAddresses($chatId)
    {
        $this->bot->sendChatAction([
            'chat_id' => $chatId,
            "action" => "typing"
        ]);

        $user = User::getUser($chatId);
        App::setLocale($user["lang"]);
        $cartUser = User::getCartUser($chatId);
        $address = $this->requestAboutCompanies->getCompanyAddresses($cartUser["company"], $cartUser["town"]);

        $reply_markup = Keyboard::button([
            'keyboard' => $address,
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
            "selective" => true,
            "remove_keyboard" => false,

        ]);
        return $this->bot->sendMessage([
            "chat_id" => $chatId,
            "text" => __("message.choose_dishes"),
            'reply_markup' => $reply_markup,
        ]);
    }
}
