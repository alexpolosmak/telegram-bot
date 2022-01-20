<?php

namespace App\Services\Telegram\Sender;

use App\Models\User;
use App\Services\DotsApi\RequestsAboutCities;
use App\Services\DotsApi\RequestsAboutCompanies;
use App\Telegram\BotInstance;
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
      //  dd("hello");
        $cartUser = User::getCartUser($chatId);
        $address = $this->requestAboutCompanies->getCompanyAddresses($cartUser["company"], $cartUser["town"]);
//dd($address);
        $reply_markup = Keyboard::button([
            'keyboard' => $address,
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
            "selective" => true,
            "remove_keyboard" => false,

        ]);
        $this->bot->sendMessage([
            "chat_id" => $chatId,
            "text" => "Chose dishes and touch on appropriate buttons",
            'reply_markup' => $reply_markup,
        ]);
    }
}
