<?php

namespace App\Services\Telegram\Sender;

use App\Services\Convertors\NameItemConvertor;
use App\Services\DotsApi\RequestsAboutCompanyItems;
use App\Telegram\BotInstance;
use GuzzleHttp\Client;
use Telegram\Bot\Actions;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;

class MenuByCompanySender
{
    private $nameItemConvertor;
    private $requestsAboutCompanyItems;
    private $bot;

    public function __construct(
        NameItemConvertor $nameItemConvertor,
        RequestsAboutCompanyItems $requestsAboutCompanyItems,
        BotInstance               $bot
    )
    {
        $this->nameItemConvertor=$nameItemConvertor;
        $this->requestsAboutCompanyItems = $requestsAboutCompanyItems;
        $this->bot = $bot->getBot();
    }

    public function sendMenuByCompany($companyId, $chatId, $categoryName)
    {
        $listCategoriesWithItems = $this->requestsAboutCompanyItems->getItemsListAsArrayByCompanyId($companyId);
        foreach ($listCategoriesWithItems as $categoryWithItems) {
            $categoryWithItems = (array)$categoryWithItems;

            if ($categoryWithItems["name"] != $categoryName)
                continue;
            foreach ($categoryWithItems["items"] as $item) {

                $item = (array)$item;
                if ($item["name"] == null)
                    continue;
                $this->bot->sendPhoto([
                    'chat_id' => $chatId,
                    'photo' => InputFile::create($item["image"], "photo"),
                    "parse_mode" => "HTML",
                    'caption' => $this->getCaptionForItem($item),
                    "reply_markup" => $this->getButtonItem($item["name"])
                ]);
            }
        }


    }

    private function getButtonItem($name)
    {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => $this->nameItemConvertor->controlItemNameLength($name), 'callback_data' => $this->nameItemConvertor->controlItemNameLength($name)]
                ]
            ]
        ];
        return json_encode($keyboard);

    }

    private function getCaptionForItem($item)
    {
        $name = $item["name"] . "\n";
        $description = $item["description"];
        $price = "Цiна: " . $item["price"] . "-UAN";
        $weight = "Вага страви: " . $item["measureText"];

        $str = "$name\n$description\n$price\n$weight";
        return "$str";
    }



}
