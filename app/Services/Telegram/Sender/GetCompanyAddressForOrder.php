<?php

namespace App\Services\Telegram\Sender;

use App\Models\User;
use App\Services\DotsApi\RequestsAboutCities;
use App\Services\DotsApi\RequestsAboutCompanies;
use App\Telegram\BotInstance;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Keyboard\Keyboard;

class GetCompanyAddressForOrder
{
    private $bot;
    private $requestAboutCompanies;
    private $requestDeliveryTime;

    public function __construct()
    {
        $this->requestAboutCompanies = new RequestsAboutCompanies(new RequestsAboutCities());
        $this->bot = (new BotInstance())->getBot();
        $this->requestDeliveryTime = new RequestDeliveryTimeSender(new BotInstance(),new RequestsAboutCompanies(new RequestsAboutCities()));
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
        $address = $this->requestAboutCompanies->getCompanyAddresses($cartUser["company"], $cartUser["town"], $user["lang"]);
        if (count($address) == 1) {
            $cartUser["addressCompany"] = $address[0][0];
            Cache::put($user["cart_id"], $cartUser);

            $this->bot->sendMessage([//доработать
                "chat_id" => $chatId,
                "text" => __("message.exist_one_example_company_adress") . $address[0][0]

            ]);
            return $this->requestDeliveryTime->sendRequestAboutDeliveryTime($user,$cartUser);
        }

        $reply_markup = Keyboard::button([
            'keyboard' => $address,
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
            "selective" => true,
            "remove_keyboard" => false,

        ]);

        return $this->bot->sendMessage([
            "chat_id" => $chatId,
            "text" => __("message.choose_company_address"),
            'reply_markup' => $reply_markup,
        ]);
    }
}
