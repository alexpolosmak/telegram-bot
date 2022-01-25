<?php

namespace App\Services\Telegram\Sender;

use App\Services\DotsApi\RequestsAboutCompanyItems;
use App\Telegram\BotInstance;
use Illuminate\Support\Facades\App;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;

class CategoriesByCompanySender
{
    private $requestsAboutCompanyItems;
    private $bot;

    public function __construct(
        RequestsAboutCompanyItems $requestsAboutCompanyItems,
        BotInstance               $bot
    )
    {
        $this->requestsAboutCompanyItems = $requestsAboutCompanyItems;
        $this->bot = $bot->getBot();
    }

    public function sendCategoriesByCompany($companyId, $chatId,$lang)
    {
        $this->bot->sendChatAction([
            'chat_id' => $chatId,
            "action"=>"typing"
        ]);

        App::setLocale($lang);
        $categories = $this->requestsAboutCompanyItems->getNamesOfCategoriesAsArrayOfArrays($companyId);

        $this->bot->sendMessage([
            'chat_id' => $chatId,
            'text' => __("message.choose_category"),
            "reply_markup" => $this->getButtonsArray($categories)
        ]);

    }

    private function getButtonsArray($categories)
    {
        $reply_markup = Keyboard::button([
            'keyboard' => $categories,
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
            "selective" => false,
            "remove_keyboard" => true


        ]);
        return $reply_markup;

    }


}
