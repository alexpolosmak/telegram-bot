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
    private $requestContactSender;
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
        LanguageSender              $languageSender


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
        $this->languageSender=$languageSender;


    }

    public function listen($updateFromTelegram)
    {
        if ($this->isCommand($updateFromTelegram)) {
            return true;
        }
        if ($this->textIsItem($updateFromTelegram)) {
            return true;
        }

        $message = $updateFromTelegram["message"];

        if (array_key_exists("contact", $message)) {
            if ($this->userService->store($message["contact"])) {
                $this->languageSender->sendRequstOnLanguage($message["chat"]["id"]);
                return true;
            }
        }
        if ($this->textIsLanguage($message)) {
            return true;
        }
        if ($this->textIsCity($message)) {
            return true;
        }
        if ($this->textIsCompany($message)) {
            return true;
        }
        if ($this->textIsCategory($message)) {
            return true;
        }
        if ($this->textIsAddress($message)) {
            return true;
        }
        if ($this->textIsDeliveryTime($message)) {
            return true;
        }
        $this->sendMessageAboutFailedRequest($message);
        return true;
    }

    private function textIsCity($message)
    {
        if (in_array($message["text"], $this->requestsAboutCities->getCitiesListAsArray())) {
            $user = User::getUser($message["chat"]["id"]);
            $cart = Cache::get($user["cart_id"]);
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

        $cartUser = Cache::get($user["cart_id"]);
        if (in_array($message["text"], $this->requestsAboutCompanies->getCompaniesListAsArrayByCity($cartUser["town"]))) {
            $cartUser["company"] = $message["text"];
            Cache::put($user["cart_id"], $cartUser);
            $this->categoriesByCompanySender->sendCategoriesByCompany(
                $this->getCompanyIdByName(
                    $message["text"],
                    $cartUser["town"]
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
        $cartUser = Cache::get($user["cart_id"]);
        if (in_array($message["text"], $this->requestsAboutCompanyItems->getNamesOfCategories($this->getCompanyIdByName($cartUser["company"], $cartUser["town"])))) {
            $categoryName = $message["text"];
            $this->menuByCompanySender->sendMenuByCompany($this->getCompanyIdByName($cartUser["company"], $cartUser["town"]), $user["chat_id"], $categoryName);
            return true;
        }

    }

    private function textIsItem($message)
    {
        $message = $message->toArray();
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
            return false;
        }

    }

    private function textIsLanguage($message)
    {

        if ($message["text"] == "english") {
            User::query()->where("chat_id", "=", $message["chat"]["id"])->update([
                "lang" => "en"
            ]);
            return $this->citiesListSender->sendCitiesList($message["chat"]["id"]);
        }
        if ($message["text"] == "ukrainian") {
            User::query()->where("chat_id", "=", $message["chat"]["id"])->update([
                "lang" => "ua"
            ]);
            return $this->citiesListSender->sendCitiesList($message["chat"]["id"]);
        }
        if ($message["text"] == "russian") {
            User::query()->where("chat_id", "=", $message["chat"]["id"])->update([
                "lang" => "ru"
            ]);
            return $this->citiesListSender->sendCitiesList($message["chat"]["id"]);

        }

        return false;
    }

    private function textIsDeliveryTime($message)
    {

        $timeFormat = "/(min.)$|(хв.)$|(мин.)$/";
        if (!preg_match($timeFormat, $message["text"])) {
            return false;
        }
        $user = $this->getUser($message["chat"]["id"])[0];
        $cartUser = Cache::get($user["cart_id"]);
        $cartUser["deliveryTime"] = $this->timeConvertor->convertTimeFromTimeDeliveryToUnix($message["text"]);
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
}
