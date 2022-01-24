<?php

namespace App\Services\Telegram\Sender;

use App\Models\User;
use App\Services\Convertors\NameItemConvertor;
use App\Services\DotsApi\RequestsAboutCompanyItems;
use App\Telegram\BotInstance;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
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
        $user=User::getUser($chatId);
        App::setLocale($user["lang"]);
        $listCategoriesWithItems = $this->requestsAboutCompanyItems->getItemsListAsArrayByCompanyId($companyId);
        Cache::put("listok",$listCategoriesWithItems);
        foreach ($listCategoriesWithItems as $categoryWithItems) {
            $categoryWithItems = (array)$categoryWithItems;
            Cache::put("ifforeach1",$listCategoriesWithItems);
            if ($categoryWithItems["name"] != $categoryName && $categoryWithItems["url"]!=$categoryName)
                continue;
            if($categoryWithItems["items"] ==""){
                $this->sendNotHaveItemsByCategory($chatId);
            }
            foreach ($categoryWithItems["items"] as $item) {
                Cache::put("ifforeach2",$item);
                $item = (array)$item;
                if ($item["name"] == "" && $item["url"] == "")
                    continue;
                $nameItem=$item["name"] != "" ? $item["name"] : $item["url"];
                $this->bot->sendPhoto([
                    'chat_id' => $chatId,
                    'photo' => InputFile::create($item["image"], "photo"),
                    "parse_mode" => "HTML",
                    'caption' => $this->getCaptionForItem($item),
                    "reply_markup" => $this->getButtonItem($nameItem,$item["id"])
                ]);
            }
        }


    }

    private function getButtonItem($name,$itemId)
    {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => $name, 'callback_data' => $itemId]
                ]
            ]
        ];//$this->nameItemConvertor->controlItemNameLength($name)
        return json_encode($keyboard);

    }

    private function getCaptionForItem($item)
    {
        $name= $item["name"]=="" ? $item["url"] : $item["name"];
        $name = $name . "\n";
        $description = $item["description"];
        $price = __("message.price_label") . $item["price"] . "-UAH";
        $weight =  __("message.weight_label"). $item["measureText"];

        $str = "$name\n$description\n$price\n$weight";
        return "$str";
    }
private function sendNotHaveItemsByCategory($chatId){
        return $this->bot->sendMessage([
            'chat_id' => $chatId,
            "text"=>__("message.have_not_item_by_category"),
        ]);
}


}
