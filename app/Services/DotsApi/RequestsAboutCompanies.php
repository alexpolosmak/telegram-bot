<?php

namespace App\Services\DotsApi;


use GuzzleHttp\Client;

class RequestsAboutCompanies
{
    private $requestAboutCities;
    private $client;

    public function __construct(
        RequestsAboutCities $requestAboutCities

    )
    {
        $this->requestAboutCities = $requestAboutCities;
        $this->client = new Client();
    }


    private function getCompanyList(string $city)
    {
        $city = $this->getCityId($city);
        //  dd($city);
        $response = $this->client->get(
            "https://clients-api.dots.live/api/v2/cities/$city/companies",
            [
                'headers' => [
                    'Api-Token' => config("configPermission")["Api_Token"],
                    'Api-Account-Token' => config("configPermission")["Api_Account_Token"],
                    'Content-Type' => config("configPermission")["Content_Type"],
                    'Accept' => config("configPermission")["Accept"],
                  'Api-Lang' => "ua"
                ],
                'query' => [
                    'v' => '2.0.0',
                ],
            ]
        );
        $body = $response->getBody();
        $body = (array)json_decode($body);
        return $body["items"];


    }

    public function getCompaniesListAsArrayByCity(string $city)
    {
        $body = $this->getCompanyList($city);
        $companyList = [];
        foreach ($body as $company) {
            $company = (array)$company;
            $companyList[] = $company["name"]=="" ?$company["url"]:$company["name"];
        }
        return $companyList;
    }

    public function getCompanyListByCityAsArrayOfArrays(string $city)
    {

        $body = $this->getCompanyList($city);
        $companiesList = [];
        foreach ($body as $company) {
            $company = (array)$company;
            $companiesList[] = (array)($company["name"] == "" ? $company["url"] : $company["name"]);
        }
     //dd($body);
        return $companiesList;
    }

    public function getCityId(string $city)
    {
        $cities = $this->requestAboutCities->getCitiesListAsArrayWithId();
        // dd($cities);
        foreach ($cities as $key => $item) {
            if ($item == $city) {
                return $key;
            }
        }
        //return false;
    }

    public function getCompanyListAsArrayWithId(string $city)
    {
        $body = $this->getCompanyList($city);

        $companiesList = [];
        foreach ($body as $company) {
            $company = (array)$company;
            $companiesList[$company["id"]] = ($company["name"] == "" ? $company["url"] : $company["name"]);
        }
        return $companiesList;


    }

    public function getCompanyIdByCompanyName($companyName, $cityName)
    {
        $companiesListWithIds = $this->getCompanyListAsArrayWithId($cityName);
 //  dd($companyName);
        foreach ($companiesListWithIds as $id => $city) {

            if ($city == $companyName) {

                return $id;
            }
        }

    }

    public function getCompanyAddresses($companyName, $cityName)
    {
        $companyId = $this->getCompanyIdByCompanyName($companyName, $cityName);
        $addressList = $this->getAddressList($companyId);

        $resultAddressList = [];
        foreach ($addressList as $address) {
            $address = (array)$address;
            $resultAddressList[][] = $address["title"];

        }
        return $resultAddressList;

    }
    public function getCompanyAddressesHowArray($companyName, $cityName)
    {
        $companyId = $this->getCompanyIdByCompanyName($companyName, $cityName);
        $addressList = $this->getAddressList($companyId);
        $resultAddressList = [];
        foreach ($addressList as $address) {
            $address = (array)$address;
            $resultAddressList[] = $address["title"];

        }
        return $resultAddressList;

    }

    public function getInfoAboutCompany($companyId)
    {
        $response = $this->client->get(
            "https://clients-api.dots.live/api/v2/companies/$companyId?v=2.0.0",
            [
                'headers' => [
                    'Api-Token' => config("configPermission")["Api_Token"],
                    'Api-Account-Token' => config("configPermission")["Api_Account_Token"],
                    'Content-Type' => config("configPermission")["Content_Type"],
                    'Accept' => config("configPermission")["Accept"],
                    'Api-Auth-Token' => config("configPermission")["Api_Account_Token"],
                    'Api-Lang' => "ua"

                ],
                'query' => [
                    'v' => '2.0.0',
                ],
            ]
        );
        $body = $response->getBody();
        $body = (array)json_decode($body);
        return $body;

    }
    public function getAddressList($companyId){

        return ($this->getInfoAboutCompany($companyId))["addresses"];
    }

    public function getAddressIdByAddressName($addressName, $companyName, $cityName)
    {
        $companyId = $this->getCompanyIdByCompanyName($companyName, $cityName);
        $addressList = $this->getAddressList($companyId);

        foreach ($addressList as $address) {
            $address = (array)$address;

            if ($address["title"] == $addressName) {

                return $address['id'];
            }

        }
    }

//    public function getDeliveryTimeByCompanyId($companyName, $cityName){
////dd("$cityName");
//        dd( $this->getCompanyIdByCompanyName($companyName, $cityName));
//
//        $info = $this->getInfoAboutCompany($companyId);
//        dd($info);
//        return 1;
//    }
}
