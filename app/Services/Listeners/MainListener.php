<?php

namespace App\Services\Listeners;

use App\Models\User;
use App\Services\Convertors\NameItemConvertor;
use App\Services\Convertors\TimeConvertor;
use App\Services\DotsApi\RequestsAboutCities;
use App\Services\DotsApi\RequestsAboutCompanies;
use App\Services\DotsApi\RequestsAboutCompanyItems;
use App\Services\Telegram\Sender\AddressWasSavedSender;
use App\Services\Telegram\Sender\CategoriesByCompanySender;
use App\Services\Telegram\Sender\CitiesListSender;
use App\Services\Telegram\Sender\CompanyByCitySender;
use App\Services\Telegram\Sender\CreateOrderSender;
use App\Services\Telegram\Sender\MenuByCompanySender;
use App\Services\Telegram\Sender\NewItemMessageSender;
use App\Services\Telegram\Sender\RequestContactSender;
use App\Services\Telegram\Sender\RequestDeliveryTimeSender;
use App\Services\Users\UserServiceInterface;
use App\Telegram\BotInstance;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Objects\Update;


class MainListener
{
    private $messages;
    private $bot;
    private $userService;
    private $requestsAboutCities;
    private $requestsAboutCompanies;
    private $menuByCompanySender;
    private $companyByCitySender;
    private $requestsAboutCompanyItems;
    private $categoriesByCompanySender;
    private $nameItemConvertor;
    private $citiesListSender;
    private $newItemMessageSender;
    private $addressWasSavedSender;
    private $requestDeliveryTimeSender;
    private $timeConvertor;
    private $createOrderSender;
    private $requestContactSender;

    public function __construct(
        UserServiceInterface      $userService,
        RequestsAboutCities       $requestsAboutCities,
        RequestsAboutCompanies    $requestsAboutCompanies,
        MenuByCompanySender       $menuByCompanySender,
        CompanyByCitySender       $companyByCitySender,
        RequestsAboutCompanyItems $requestsAboutCompanyItems,
        CategoriesByCompanySender $categoriesByCompanySender,
        NameItemConvertor         $nameItemConvertor,
        CitiesListSender          $citiesListSender,
        NewItemMessageSender      $newItemMessageSender,
        AddressWasSavedSender     $addressWasSavedSender,
        RequestDeliveryTimeSender $requestDeliveryTimeSender,
        TimeConvertor             $timeConvertor,
        CreateOrderSender         $createOrderSender,
        RequestContactSender      $requestContactSender


    )
    {
        $this->companyByCitySender = $companyByCitySender;
        $this->menuByCompanySender = $menuByCompanySender;
        $this->requestsAboutCompanies = $requestsAboutCompanies;
        $this->requestsAboutCities = $requestsAboutCities;
        $this->userService = $userService;
        $this->bot = (new BotInstance())->getBot();
        Cache::put("bot", $this->bot->getWebhookUpdate());
        $this->messages = $this->bot->getWebhookUpdate();

        $this->requestsAboutCompanyItems = $requestsAboutCompanyItems;
        $this->categoriesByCompanySender = $categoriesByCompanySender;
        $this->nameItemConvertor = $nameItemConvertor;
        $this->citiesListSender = $citiesListSender;
        $this->newItemMessageSender = $newItemMessageSender;
        $this->addressWasSavedSender = $addressWasSavedSender;
        $this->requestDeliveryTimeSender = $requestDeliveryTimeSender;
        $this->timeConvertor = $timeConvertor;
        $this->createOrderSender = $createOrderSender;
        $this->requestContactSender = $requestContactSender;


    }

    public function listen()
    {
        Cache::put("mess1", $this->messages);

        if ($this->isCommand()) {
            return true;
        }
        //  Cache::put("item", $this->messages->toArray());

        Cache::put("ITEM", "true");
        if ($this->textIsItem($this->messages)) {
            return true;
        }

        //    Cache::put("arround", $this->messages);
        $message = $this->messages["message"];
        //   $message = (array)$message;

        Cache::put("connn", "true1");
        if (array_key_exists("contact", $message)) {
            Cache::put("if1", "true1");
            if ($this->userService->store($message["contact"])) {
                Cache::put("if2", "true");
                Cache::put("al  ", $message["chat"]["id"]);
                $this->citiesListSender->sendCitiesList($message["chat"]["id"]);

                return true;
            }
        }
        if ($this->textIsLanguage($message)) {
            return true;
        }
        Cache::put("City", "123");
        if ($this->textIsCity($message)) {
            return true;
        }
        Cache::put("company", "321");
        if ($this->textIsCompany($message)) {
            return true;
        }
        Cache::put("category", "2222");
        if ($this->textIsCategory($message)) {
            return true;
        }
        Cache::put("address", "true");
        if ($this->textIsAddress($message)) {
            return true;
        }
        Cache::put("time", "123");
        if ($this->textIsDeliveryTime($message)) {
            Cache::put("inText", "true");
            return true;
        }
        Cache::put("aka", "true");


//        $this->textIsAddress($message);

        return true;
    }

    private function textIsCity($message)
    { //Cache::put("city",$message);
//        Cache::put("cities", $this->requestsAboutCities->getCitiesListAsArray());
//        Cache::put("data2", $message["text"]);
        if (in_array($message["text"], $this->requestsAboutCities->getCitiesListAsArray())) {
          //  Cache::put("city12", $message["text"]);
            $user = User::getUser($message["chat"]["id"]);
          //  $cartId = ($user->toArray())[0]["cart_id"];
            $cart = Cache::get($user["cart_id"]);
            $cart["town"] = $message["text"];
            Cache::put($user["cart_id"], $cart);
//            Cache::put("id", $message["chat"]["id"]);
//            Cache::put("town", $message["text"]);

            $this->companyByCitySender->sendCompaniesListByCity($message["text"], $message["chat"]["id"],$user["lang"]);
            return true;
        }
        return false;
    }

    private function textIsCompany($message)
    {
        $user = User::getUser($message["chat"]["id"]);
        Cache::put("1111",$user);
        $cartUser = Cache::get($user["cart_id"]);
        Cache::put("companies", $this->requestsAboutCompanies->getCompaniesListAsArrayByCity($cartUser["town"]));
        Cache::put("rrrr", $message["text"]);
      //  Cache::put("trueCom12",true);
        if (in_array($message["text"], $this->requestsAboutCompanies->getCompaniesListAsArrayByCity($cartUser["town"]))) {
            Cache::put("trueCom1",false);
            $cartUser["company"] = $message["text"];
            Cache::put($user["cart_id"], $cartUser);
            $this->categoriesByCompanySender->sendCategoriesByCompany($this->getCompanyIdByName($message["text"], $cartUser["town"]), $user["chat_id"], $user["lang"]);
            return true;
        }
        return false;
    }

    public function textIsCategory($message)
    {
        $user = User::getUser($message["chat"]["id"]);
        $cartUser = Cache::get($user["cart_id"]);
        Cache::put("truecom",$message["text"]);
        Cache::put("trueComl", $this->requestsAboutCompanyItems->getNamesOfCategories($this->getCompanyIdByName($cartUser["company"], $cartUser["town"])));
        //  dd($this->requestsAboutCompanies->getCityId($cartUser["town"]));
        // dd($this->getCompanyIdByName($cartUser["company"], $cartUser["town"]));
        //  dd( $this->requestsAboutCompanyItems->getNamesOfCategories($this->requestsAboutCompanies->getCityId($cartUser["town"])));
        if (in_array($message["text"], $this->requestsAboutCompanyItems->getNamesOfCategories($this->getCompanyIdByName($cartUser["company"], $cartUser["town"])))) {
            $categoryName = $message["text"];
            $this->menuByCompanySender->sendMenuByCompany($this->getCompanyIdByName($cartUser["company"], $cartUser["town"]), $user["chat_id"], $categoryName);
            return true;
        }

    }

    private function textIsItem($message)
    {
        Cache::put("call1", $message);
        $message = $message->toArray();
        Cache::put("callback_query", array_key_exists("callback_query", $message));
        if (!array_key_exists("callback_query", $message))
            return false;

        $user = User::getUser($message["callback_query"]["from"]["id"]);
        $cartUser = Cache::get($user["cart_id"]);
        $item = $message["callback_query"]["data"];

            if (!$this->orderItemAlreadyExist($item, $cartUser)) {
                $cartUser["items"][] = ["id" => $item, "count" => 1];
                Cache::put($user["cart_id"], $cartUser);
                $this->newItemMessageSender->sendMessageAboutNewItem($user["chat_id"], $message["callback_query"]["data"]);

            } else {
                $cartUser = $this->addCountItems($item, $cartUser);
                Cache::put($user["cart_id"], $cartUser);
                $this->newItemMessageSender->sendMessageAboutNewItem($user["chat_id"], $message["callback_query"]["data"]);
            }
            return true;


    }


    private function getUser($chatId): array
    {
        return (User::query()->where("chat_id", "=", $chatId)->get())->toArray();
    }

    private function getCompanyIdByName($companyName, $cityName)
    {
        $companies = $this->requestsAboutCompanies->getCompanyListAsArrayWithId($cityName);
        foreach ($companies as $key => $company) {
            if ($company == $companyName) {
                return $key;
            }

        }
    }

    private function getCityIdByName($cityName)
    {
        $cities = $this->requestsAboutCities->getCitiesListAsArrayWithId();
        foreach ($cities as $key => $city) {
            if ($city == $cityName) {
                return $key;
            }

        }
    }

    private function getItemIdByName(string $itemName, array $itemsList)
    {
        foreach ($itemsList as $id => $item) {
            if ($item == $itemName) {
                return $id;
            }
        }

    }

    private function orderItemAlreadyExist(string $itemId, $cartUser)
    {
        $items = $cartUser["items"];
        foreach ($items as $item) {
            if ($item["id"] == $itemId) {
                return true;
            }

        }
        return false;
    }

    private function addCountItems(string $itemId, $cartUser)
    {
        $items = $cartUser["items"];
        foreach ($items as $key => $item) {
            if ($item["id"] == $itemId) {
                $cartUser["items"][$key]["count"] = $cartUser["items"][$key]["count"] + 1;

                return $cartUser;
            }

        }
    }

    private function textIsAddress($message)
    {
        $user = $this->getUser($message["chat"]["id"])[0];
        $cartUser = Cache::get($user["cart_id"]);
        $addresses = $this->requestsAboutCompanies->getCompanyAddressesHowArray($cartUser["company"], $cartUser["town"]);
        $address = $message['text'];

        if (in_array($address, $addresses)) {

            $cartUser["addressCompany"] = $address;
            Cache::put($user["cart_id"], $cartUser);
            $this->addressWasSavedSender->sendMessageAboutAddressWasSaved($user["chat_id"], $user["lang"]);
            $this->requestDeliveryTimeSender->sendRequestAboutDeliveryTime($user, $cartUser);
            return true;
        } else {
            // $this->addressWasSavedSender->sendMessageAboutAddressWasNotSaved($user["chat_id"]);
            return false;
        }

    }

    private function textIsLanguage($message)
    {

        if ($message["text"] == "english") {
            User::query()->where("chat_id", "=", $message["chat"]["id"])->update([
                "lang" => "en"
            ]);
            return $this->requestContactSender->sendRequestContact($message["chat"]["id"]);
        }
        if ($message["text"] == "ukrainian") {
            User::query()->where("chat_id", "=", $message["chat"]["id"])->update([
                "lang" => "ua"
            ]);
            return $this->requestContactSender->sendRequestContact($message["chat"]["id"]);
        }
        if ($message["text"] == "russian") {
            User::query()->where("chat_id", "=", $message["chat"]["id"])->update([
                "lang" => "ru"
            ]);
            return $this->requestContactSender->sendRequestContact($message["chat"]["id"]);
        }

        return false;
    }

    private function textIsDeliveryTime($message)
    {
        //   return true;
        Cache::put("y", $message);
        $timeFormat = "/(min.)$|(хв.)$|(мин.)$/";
        Cache::put("preg1", "true");
        Cache::put("pregRes", preg_match($timeFormat, $message["text"]));
        if (!preg_match($timeFormat, $message["text"])) {
            Cache::put("preg", "true2");
            return false;
        }
        $user = $this->getUser($message["chat"]["id"])[0];
        $cartUser = Cache::get($user["cart_id"]);
        $cartUser["deliveryTime"] = $this->timeConvertor->convertTimeFromTimeDeliveryToUnix($message["text"]);
        Cache::put($user["cart_id"], $cartUser);
        $this->createOrderSender->sendInviteForCreateOrder($message["chat"]["id"]);
        return true;

    }

    private function shortNameOfItemExistsInItemsList(&$item, $itemList)
    {
        foreach ($itemList as $key => $longItem) {
            if (
                $this->nameItemConvertor->controlItemNameLength($longItem) == $item

            ) {
                Cache::put("lm", $longItem);
                Cache::put("longItem1", $this->nameItemConvertor->controlItemNameLength($longItem));
                $item = $longItem;
                return true;
            }

        }
        return false;
    }

    private function isCommand()
    {
        if (!array_key_exists("message", $this->messages->toArray()) || !array_key_exists("entities", $this->messages->toArray()["message"]))
            return false;
        $type = $this->messages->toArray()["message"]["entities"][0]["type"];
        if ($type == "bot_command") {

            return true;
        }
        return false;

    }
}
