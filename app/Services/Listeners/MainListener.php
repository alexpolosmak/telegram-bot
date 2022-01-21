<?php

namespace App\Services\Listeners;

use App\Models\User;
use App\Services\Convertors\NameItemConvertor;
use App\Services\DotsApi\RequestsAboutCities;
use App\Services\DotsApi\RequestsAboutCompanies;
use App\Services\DotsApi\RequestsAboutCompanyItems;
use App\Services\Telegram\Sender\CategoriesByCompanySender;
use App\Services\Telegram\Sender\CompanyByCitySender;
use App\Services\Telegram\Sender\MenuByCompanySender;
use App\Services\Users\UserServiceInterface;
use App\Telegram\BotInstance;
use Illuminate\Support\Facades\Cache;


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

    public function __construct(
        UserServiceInterface      $userService,
        RequestsAboutCities       $requestsAboutCities,
        RequestsAboutCompanies    $requestsAboutCompanies,
        MenuByCompanySender       $menuByCompanySender,
        CompanyByCitySender       $companyByCitySender,
        RequestsAboutCompanyItems $requestsAboutCompanyItems,
        CategoriesByCompanySender $categoriesByCompanySender,
        NameItemConvertor $nameItemConvertor
    )
    {
        $this->companyByCitySender = $companyByCitySender;
        $this->menuByCompanySender = $menuByCompanySender;
        $this->requestsAboutCompanies = $requestsAboutCompanies;
        $this->requestsAboutCities = $requestsAboutCities;
        $this->userService = $userService;
        $this->bot = (new BotInstance())->getBot();
        $this->messages = $this->bot->getWebhookUpdate();
        $this->requestsAboutCompanyItems = $requestsAboutCompanyItems;
        $this->categoriesByCompanySender = $categoriesByCompanySender;
        $this->nameItemConvertor=$nameItemConvertor;
    }

    public function listen()
    {
        foreach ($this->messages as $message) {

            if ($this->textIsItem($message)) {
                return;
            }

            if (array_key_exists("contact", $message["message"])) {
                $this->userService->store($message);
                return;
            }
            $this->textIsCity($message);
            $this->textIsCompany($message);
            $this->textIsCategory($message);
            $this->textIsAddress($message);
        }


    }

    private function textIsCity($message)
    {
        if (in_array($message["message"]["text"], $this->requestsAboutCities->getCitiesListAsArray())) {
            $user = User::query()->where("chat_id", "=", $message["message"]["chat"]["id"])->get();
            $cartId = ($user->toArray())[0]["cart_id"];
            $cart = Cache::get($cartId);
            $cart["town"] = $message["message"]["text"];
            Cache::put($cartId, $cart);
            $this->companyByCitySender->sendCompaniesListByCity($message["message"]["text"], $message["message"]["chat"]["id"]);
        }
    }

    private function textIsCompany($message)
    {
        $user = $this->getUser($message["message"]["chat"]["id"])[0];
        $cartUser = Cache::get($user["cart_id"]);
        if (in_array($message["message"]["text"], $this->requestsAboutCompanies->getCompaniesListAsArrayByCity($cartUser["town"]))) {
            $cartUser["company"] = $message["message"]["text"];
            Cache::put($user["cart_id"], $cartUser);
            $this->categoriesByCompanySender->sendCategoriesByCompany($this->getCompanyIdByName($message["message"]["text"], $cartUser["town"]), $user["chat_id"]);
        }
    }

    public function textIsCategory($message)
    {
        $user = $this->getUser($message["message"]["chat"]["id"])[0];
        $cartUser = Cache::get($user["cart_id"]);
        //  dd($this->requestsAboutCompanies->getCityId($cartUser["town"]));
       // dd($this->getCompanyIdByName($cartUser["company"], $cartUser["town"]));
      //  dd( $this->requestsAboutCompanyItems->getNamesOfCategories($this->requestsAboutCompanies->getCityId($cartUser["town"])));
        if (in_array($message["message"]["text"], $this->requestsAboutCompanyItems->getNamesOfCategories($this->getCompanyIdByName($cartUser["company"], $cartUser["town"])))) {
            $categoryName = $message["message"]["text"];
            $this->menuByCompanySender->sendMenuByCompany($this->getCompanyIdByName($cartUser["company"], $cartUser["town"]), $user["chat_id"], $categoryName);
        }

    }

    private function textIsItem($message)
    {

        $message = $message->toArray();
        if (!array_key_exists("callback_query", $message))
            return false;
        $user = $this->getUser($message["callback_query"]["from"]["id"])[0];
        $cartUser = Cache::get($user["cart_id"]);
        $item = $message["callback_query"]["data"];
        $itemsList = $this->requestsAboutCompanyItems->getItemsNameList($this->getCompanyIdByName($cartUser["company"], $cartUser["town"]));
     //  dd($item);
        //dd($itemsList);
        if (in_array($item, $itemsList) ||$this->shortNameOfItemExistsInItemsList($item, $itemsList)
        ) {

            $item = $this->getItemIdByName($item, $itemsList);

            if (!$this->orderItemAlreadyExist($item, $cartUser)) {
                $cartUser["items"][] = ["id" => $item, "count" => 1];

                Cache::put($user["cart_id"], $cartUser);
            } else {
                $cartUser = $this->addCountItems($item, $cartUser);
                Cache::put($user["cart_id"], $cartUser);
            }

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
        $user = $this->getUser($message["message"]["chat"]["id"])[0];
        $cartUser = Cache::get($user["cart_id"]);
        $addresses = $this->requestsAboutCompanies->getCompanyAddressesHowArray($cartUser["company"], $cartUser["town"]);
        $address = $message["message"]['text'];

        if (in_array($address, $addresses)) {

            $cartUser["addressCompany"] = $address;
            Cache::put($user["cart_id"], $cartUser);
        }
    }
    private function shortNameOfItemExistsInItemsList(&$item,$itemList){
        foreach ($itemList as $longItem){
            if(
                $this->nameItemConvertor->controlItemNameLength($longItem)==$item
            ){
                $item= $longItem;
                return true;
            }

        }
        return false;
    }
}
