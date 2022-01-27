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
use App\Services\Telegram\Sender\LanguageSender;
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
    // private $requestContactSender;
    private $languageSender;

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
        RequestContactSender      $requestContactSender,
        LanguageSender            $languageSender


    )
    {
        $this->companyByCitySender = $companyByCitySender;
        $this->menuByCompanySender = $menuByCompanySender;
        $this->requestsAboutCompanies = $requestsAboutCompanies;
        $this->requestsAboutCities = $requestsAboutCities;
        $this->userService = $userService;
        $this->bot = (new BotInstance())->getBot();
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
        $this->languageSender = $languageSender;


    }

    public function listen($updateFromTelegram)
    {

        Cache::put("user11", $updateFromTelegram);
        if ($this->isCommand($updateFromTelegram)) {
            return true;
        }
        if ($this->textIsItem($updateFromTelegram)) {
            return true;
        }
        if($this->inputTimeInUnixFormat($updateFromTelegram)){
            return true;
        }
        $message = $updateFromTelegram->toArray();
        $message = $message["message"];

        Cache::put("ArrContect", true);
        if (array_key_exists("contact", $message)) {
            if ($this->userService->store($message["contact"])) {
                Cache::put("InContect", true);
                $this->languageSender->sendRequstOnLanguage($message["chat"]["id"]);
                return true;
            }
            return true;
        }
        if ($this->textIsLanguage($message)) {
            return true;
        }

        if ($this->textIsCity($message)) {
            return true;
        }
        Cache::put("afterLan1", $message);
        if ($this->textIsCompany($message)) {
            return true;
        }
        Cache::put("afterLan2", $message);
        if ($this->textIsCategory($message)) {
            return true;
        }
        Cache::put("afterLan31", $message);
        if ($this->textIsAddress($message)) {
            return true;
        }

        Cache::put("afterLan4", $message);
        if ($this->textIsDeliveryTime($message)) {
            return true;
        }
        Cache::put("afterLan5", $message);
        $this->sendMessageAboutFailedRequest($message);
        Cache::put("afterLan6", $message);
        return true;
    }

    private function textIsCity($message)
    {
        $user = User::getUser($message["chat"]["id"]);
        if ($user == []) {
            return false;
        }
        $cart = Cache::get($user["cart_id"]);
        if (in_array($message["text"], $this->requestsAboutCities->getCitiesListAsArray($user['lang']))) {
            $cart["town"] = $message["text"];
            Cache::put($user["cart_id"], $cart);
            $this->companyByCitySender->sendCompaniesListByCity($message["text"], $message["chat"]["id"], $user["lang"]);
            return true;
        }
        return false;
    }

    private function textIsCompany($message)
    {
        $user = User::getUser($message["chat"]["id"]);
        if ($user == [])
            return false;

        $cartUser = Cache::get($user["cart_id"]);
        if ($cartUser["town"] == "")
            return false;
        if (in_array($message["text"], $this->requestsAboutCompanies->getCompaniesListAsArrayByCity($cartUser["town"], $user["lang"]))) {
            $cartUser["company"] = $message["text"];
            Cache::put($user["cart_id"], $cartUser);
            $this->categoriesByCompanySender->sendCategoriesByCompany(
                $this->getCompanyIdByName(
                    $message["text"],
                    $cartUser["town"],
                    $user["lang"]
                ),
                $user["chat_id"],
                $user["lang"]
            );
            return true;
        }
        return false;
    }

    private function textIsCategory($message)
    {
        $user = User::getUser($message["chat"]["id"]);
        if ($user == []) {
            return false;
        }
        $cartUser = Cache::get($user["cart_id"]);
        if ($cartUser["town"] == "" || $cartUser["company"] == "") {
            return false;
        }
        Cache::put("ctct", $this->requestsAboutCompanyItems->getNamesOfCategories($this->getCompanyIdByName($cartUser["company"], $cartUser["town"], $user["lang"])));
        if (in_array($message["text"], $this->requestsAboutCompanyItems->getNamesOfCategories($this->getCompanyIdByName($cartUser["company"], $cartUser["town"], $user["lang"]), $user["lang"]))) {
            $categoryName = $message["text"];
            $this->menuByCompanySender->sendMenuByCompany($this->getCompanyIdByName($cartUser["company"], $cartUser["town"], $user["lang"]), $user["chat_id"], $categoryName, $user["lang"]);
            Cache::put("ctct1", $message);
            return true;
        }

    }

    private function textIsItem($message)
    {
        $message = $message->toArray();
        if (!array_key_exists("callback_query", $message))
            return false;
        if(is_numeric($message["callback_query"]["data"]))
            return false;

        $user = User::getUser($message["callback_query"]["from"]["id"]);
        $cartUser = Cache::get($user["cart_id"]);
        $item = $message["callback_query"]["data"];

        if (!$this->orderItemAlreadyExist($item, $cartUser)) {
            $cartUser["items"][] = ["id" => $item, "count" => 1];
            Cache::put($user["cart_id"], $cartUser);

            $this->newItemMessageSender->sendMessageAboutNewItem($user["chat_id"], $message["callback_query"]["data"], $cartUser["town"], $cartUser["company"], $user["lang"]);

        } else {
            $cartUser = $this->addCountItems($item, $cartUser);
            Cache::put($user["cart_id"], $cartUser);
            $this->newItemMessageSender->sendMessageAboutNewItem($user["chat_id"], $message["callback_query"]["data"], $cartUser["town"], $cartUser["company"], $user["lang"]);
        }
        return true;


    }


    private function getUser($chatId): array
    {
        return (User::query()->where("chat_id", "=", $chatId)->get())->toArray();
    }

    private function getCompanyIdByName($companyName, $cityName, $lang)
    {
        $companies = $this->requestsAboutCompanies->getCompanyListAsArrayWithId($cityName, $lang);
        foreach ($companies as $key => $company) {
            if ($company == $companyName) {
                return $key;
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
        $user = User::getUser($message["chat"]["id"]);
        if ($user == []) {
            return false;
        }
        $cartUser = Cache::get($user["cart_id"]);
        if ($cartUser["town"] == "" || $cartUser["company"] == "")
            return false;
        $addresses = $this->requestsAboutCompanies->getCompanyAddressesHowArray($cartUser["company"], $cartUser["town"], $user["lang"]);
        $address = $message['text'];

        if (in_array($address, $addresses)) {

            $cartUser["addressCompany"] = $address;
            Cache::put($user["cart_id"], $cartUser);
            $this->addressWasSavedSender->sendMessageAboutAddressWasSaved($user["chat_id"], $user["lang"]);
            $this->requestDeliveryTimeSender->sendRequestAboutDeliveryTime($user, $cartUser);
            return true;
        } else {
            return false;
        }

    }

    private function textIsLanguage($message)
    {

        if ($message["text"] == "English") {
            User::query()->where("chat_id", "=", $message["chat"]["id"])->update([
                "lang" => "en"
            ]);
            return $this->citiesListSender->sendCitiesList($message["chat"]["id"]);
        }
        if ($message["text"] == "Ukrainian") {
            User::query()->where("chat_id", "=", $message["chat"]["id"])->update([
                "lang" => "ua"
            ]);
            return $this->citiesListSender->sendCitiesList($message["chat"]["id"]);
        }
        if ($message["text"] == "Russian") {
            User::query()->where("chat_id", "=", $message["chat"]["id"])->update([
                "lang" => "ru"
            ]);
            return $this->citiesListSender->sendCitiesList($message["chat"]["id"]);

        }

        return false;
    }

    private function textIsDeliveryTime($message)
    {

        $timeFormatWithoutDate = "/^[0-9][0-9]:[0-9][0-9]$/";
        $timeFormatWithDate = "/^[0-9][0-9]:[0-9][0-9] [0-9][0-9]\.[0-9][0-9]$/";
        //   $timeFormat = "/(min.)$|(хв.)$|(мин.)$/";
        if (
            (preg_match($timeFormatWithoutDate, $message["text"]) == preg_match($timeFormatWithDate, $message["text"]))
        ) {
            //Cache::put("inPreg123",!preg_match($timeFormatWithoutDate, $message["text"]));
            return false;
        }
        date_default_timezone_set('Europe/Kiev');
        $user = $this->getUser($message["chat"]["id"])[0];
        $cartUser = Cache::get($user["cart_id"]);
       if( str_contains($message["text"],".")){
           $time= $this->timeConvertor->convertTimeFromHourAndMinutesAndDaysFormat($message["text"])+ 90;
       }else{
           $time = $this->timeConvertor->convertTimeFromHourAndMinutesFormat($message["text"])+ 90;
       }

        if ($time < time()) {
            return false;
        }
        $cartUser["deliveryTime"] =$time;

            Cache::put("delTime", $cartUser["deliveryTime"]);
        Cache::put($user["cart_id"], $cartUser);
        $this->createOrderSender->sendInviteForCreateOrder($message["chat"]["id"]);
        return true;

    }


    private function isCommand($updateFromTelegram)
    {
        if (!array_key_exists("message", $updateFromTelegram->toArray()) || !array_key_exists("entities", $updateFromTelegram->toArray()["message"]))
            return false;
        $type = $updateFromTelegram->toArray()["message"]["entities"][0]["type"];
        if ($type == "bot_command") {

            return true;
        }
        return false;

    }

    private function sendMessageAboutFailedRequest($message)
    {
        $this->bot->sendMessage([
            "chat_id" => $message["chat"]["id"],
            "text" => __("message.failed_request")

        ]);
    }


    private function inputTimeInUnixFormat($message){
        if (!array_key_exists("callback_query", $message))
            return false;
        date_default_timezone_set('Europe/Kiev');
        $message=$message->toArray();
        if(!is_numeric($message["callback_query"]["data"])){
            return false;
        }
        if(date("Y",$message["callback_query"]["data"])!=date("Y")){
            return false;
        }
        if($message["callback_query"]["data"]<time()){
            return false;
        }

        $user=User::getUser($message["callback_query"]["from"]["id"]);
        Cache::put("userr",$user);
        $key=$user["cart_id"];
        Cache::put("key",$key);
        $cartUser=Cache::get($user["cart_id"]);
        Cache::put("userc",$cartUser);
        $cartUser["deliveryTime"]=$message["callback_query"]["data"];
        return true;

    }
}
