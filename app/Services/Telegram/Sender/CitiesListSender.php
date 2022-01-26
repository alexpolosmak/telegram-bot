<?php

namespace App\Services\Telegram\Sender;


use App\Models\User;
use App\Services\DotsApi\RequestsAboutCities;
use App\Telegram\BotInstance;
use Illuminate\Support\Facades\App;
use Telegram\Bot\Actions;
use Telegram\Bot\Keyboard\Keyboard;

class CitiesListSender
{
    private $apiCompanyServices;
    private $bot;


    public function __construct(
        RequestsAboutCities $requestsAboutCities,
        BotInstance         $bot
    )
    {
        $this->apiCompanyServices = $requestsAboutCities;
        $this->bot = $bot->getBot();
    }

    public function sendCitiesList($chatId)
    {
        $user = User::getUser($chatId);
        if ($user != []) {
            App::setLocale($user["lang"]);
            $citiesList = $this->apiCompanyServices->getCitiesListAsArrayOfArrays($user["lang"]);
        }
        $this->bot->sendChatAction([
            'chat_id' => $chatId,
            "action" => "typing"
        ]);

        $reply_markup = Keyboard::button([
            'keyboard' => $citiesList,
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
            "selective" => false,
            "remove_keyboard" => true


        ]);

        return $this->bot->sendMessage([
            'chat_id' => $chatId,
            'text' => __("message.enter_city"),
            'reply_markup' => $reply_markup
        ]);

    }
}
