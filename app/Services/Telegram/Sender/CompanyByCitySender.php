<?php

namespace App\Services\Telegram\Sender;

use App\Models\User;
use App\Services\DotsApi\RequestsAboutCompanies;
use App\Telegram\BotInstance;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
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
    public function sendCompaniesListByCity($cityName, $chatId,$lang)
    {
        $this->bot->sendChatAction([
            'chat_id' => $chatId,
            "action"=>"typing"
        ]);

        App::setLocale($lang);
        $companiesList=$this->requestsAboutCompanies->getCompanyListByCityAsArrayOfArrays($cityName,$lang);
        $reply_markup = Keyboard::button([
            'keyboard' => $companiesList,
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
            "selective" => true,
            "remove_keyboard" => false,

        ]);
        $this->bot->sendMessage([
            "chat_id" => $chatId,
            "text" => __("message.choose_company"),
            'reply_markup' => $reply_markup,
        ]);

    }
}
