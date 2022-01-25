<?php

namespace App\Services\Telegram\Sender;

use App\Models\User;
use App\Services\DotsApi\RequestsAboutCities;
use App\Services\DotsApi\RequestsAboutCompanies;
use App\Services\DotsApi\RequestsAboutCompanyItems;
use App\Telegram\BotInstance;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;


class NewItemMessageSender
{

    private $bot;

private $requestsAboutCompanyItems;
    public function __construct(
        BotInstance $bot,
        RequestsAboutCompanies $requestsAboutCompanies,
        RequestsAboutCompanyItems  $requestsAboutCompanyItems
    )
    {
        $this->bot = $bot->getBot();
        $this->requestsAboutCompanyItems=$requestsAboutCompanyItems;

    }

    public function sendMessageAboutNewItem($chatId, $idItem,$cityName,$companyName)
    {

      $nameItem=  $this->requestsAboutCompanyItems->getNameItemByIdItem($cityName,$companyName,$idItem);


        $this->bot->sendChatAction([
            'chat_id' => $chatId,
            "action" => "typing"
        ]);
        $user = User::getUser($chatId);
        App::setLocale($user["lang"]);
        $this->bot->sendChatAction([
            'chat_id' => $chatId,
            "action" => "typing"
        ]);
        $text = __("message.new_dish_first_item") . $nameItem . __("message.new_dish_second_item");
        return $this->bot->sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => "HTML"

        ]);


    }
}
