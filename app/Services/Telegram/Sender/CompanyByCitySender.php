<?php

namespace App\Services\Telegram\Sender;

use App\Services\DotsApi\RequestsAboutCompanies;
use App\Telegram\BotInstance;
use Telegram\Bot\Keyboard\Keyboard;

class CompanyByCitySender
{
    private $requestsAboutCompanies;
    private $bot;
    public function __construct(
        RequestsAboutCompanies $requestsAboutCompanies,
        BotInstance               $bot
    )
    {
        $this->requestsAboutCompanies = $requestsAboutCompanies;
        $this->bot = $bot->getBot();
    }
    public function sendCompaniesListByCity($cityName, $chatId)
    {
        $companiesList=$this->requestsAboutCompanies->getCompanyListByCityAsArrayOfArrays($cityName);
        $reply_markup = Keyboard::button([
            'keyboard' => $companiesList,
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
