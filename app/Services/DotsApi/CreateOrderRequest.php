<?php

namespace App\Services\DotsApi;

use App\Models\User;
use App\Services\Convertors\TimeConvertor;
use App\Services\Telegram\Sender\CompanyByCitySender;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;


class CreateOrderRequest
{
    private $requestAboutCities;
    private $requestAboutCompanies;
    private $convertorTime;

    // private $companyByCitySender;
    public function __construct()
    {
        $this->convertorTime = new TimeConvertor();
        // $this->companyByCitySender=new CompanyByCitySender();
        $this->requestAboutCities = new RequestsAboutCities();
        $this->requestAboutCompanies = new RequestsAboutCompanies(new RequestsAboutCities());

    }

    public function createOrder($chatId)
    {

        $cartUser = User::getCartUser($chatId);

        $cityId = $this->requestAboutCities->getCityIdByCitName($cartUser["town"]);
        $companyId = $this->requestAboutCompanies->getCompanyIdByCompanyName($cartUser["company"], $cartUser["town"]);
        $companyAddressId = $this->requestAboutCompanies->getAddressIdByAddressName($cartUser["addressCompany"], $cartUser["company"], $cartUser["town"]);
        //  dd(($this->requestAboutCompanies->getInfoAboutCompany($companyId))["deliveryTime"]);
        //$deliveryTime = $this->getDeliveryTime($companyId);
        //$deliveryTime = $this->convertorTime->convertTimeFromTimeDeliveryToUnix($deliveryTime);

        $data['orderFields'] = [
            'cityId' => $this->requestAboutCities->getCityIdByCitName($cartUser["town"]),
            'companyId' => $this->requestAboutCompanies->getCompanyIdByCompanyName($cartUser["company"], $cartUser["town"]),
            "companyAddressId" => $this->requestAboutCompanies->getAddressIdByAddressName($cartUser["addressCompany"], $cartUser["company"], $cartUser["town"]),
            'userName' => $cartUser["name"],
            'userPhone' => $cartUser["number_phone"],
            'deliveryType' => 2,
            'paymentType' => 1,
            'deliveryTime' => $cartUser["deliveryTime"],
            'cartItems' => $cartUser["items"]
        ];


        Cache::put("order", json_encode($data));
        $client = new Client();
        $response = $client->post(
            'https://clients-api.dots.live/api/v2/orders',
            [
                'headers' => [
                    'Api-Auth-Token' => config("configPermission")["Api_Auth_Token"],
                    'Api-Token' => config("configPermission")["Api_Token"],
                    'Api-Account-Token' => config("configPermission")["Api_Account_Token"],
                    'Content-Type' => config("configPermission")["Content_Type"],
                    'Accept' => config("configPermission")["Accept"],
                    "Api-lang"=>"ua"


                ],
                'query' => [
                    'v' => '2.0.0',
                ],
                'json' => $data,
            ]
        );
        Cache::put("response", $response);
        //  dd($response);
        //    $body = $response->getBody();

    }

    private function getDeliveryTime($companyId)
    {


        return ($this->requestAboutCompanies->getInfoAboutCompany($companyId))["deliveryTime"] == null ? 0 : (($this->requestAboutCompanies->getInfoAboutCompany($companyId))["deliveryTime"])->label;
    }

}
