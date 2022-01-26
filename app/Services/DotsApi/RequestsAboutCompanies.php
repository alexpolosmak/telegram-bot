<?php

namespace App\Services\DotsApi;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

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


    public function getCompanyList(string $city,string $lang="en")
    {
        $city = $this->getCityId($city,$lang);
        //  dd($city);
        $response = $this->client->get(
            "https://clients-api.dots.live/api/v2/cities/$city/companies",
            [
                'headers' => [
                    'Api-Token' => config("configPermission")["Api_Token"],
                    'Api-Account-Token' => config("configPermission")["Api_Account_Token"],
                    'Content-Type' => config("configPermission")["Content_Type"],
                    'Accept' => config("configPermission")["Accept"],
                  'Api-Lang' => $lang
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

    public function getCompaniesListAsArrayByCity(string $city,$lang="en")
    {

        $body = $this->getCompanyList($city,$lang);
        Cache::put("bod",$body);
        $companyList = [];
        foreach ($body as $company) {
            $company = (array)$company;
            $companyList[] = $company["name"]=="" ? $company["url"] : $company["name"];
        }
        return $companyList;
    }

    public function getCompanyListByCityAsArrayOfArrays(string $city,string $lang="en")
    {

        $body = $this->getCompanyList($city,$lang);
        $companiesList = [];
        foreach ($body as $company) {
            $company = (array)$company;
            $companiesList[] = (array)($company["name"] == "" ? $company["url"] : $company["name"]);
        }
     //dd($body);
        return $companiesList;
    }

    public function getCityId(string $city,string $lang="en")
    {
        $cities = $this->requestAboutCities->getCitiesListAsArrayWithId($lang);
        // dd($cities);
        foreach ($cities as $key => $item) {
            if ($item == $city) {
                return $key;
            }
        }
        //return false;
    }

    public function getCompanyListAsArrayWithId(string $city,string $lang="en")
    {
        $body = $this->getCompanyList($city,$lang);

        $companiesList = [];
        foreach ($body as $company) {
            $company = (array)$company;
            $companiesList[$company["id"]] = ($company["name"] == "" ? $company["url"] : $company["name"]);
        }
        return $companiesList;


    }

    public function getCompanyIdByCompanyName($companyName, $cityName,$lang="en")
    {
        $companiesListWithIds = $this->getCompanyListAsArrayWithId($cityName,$lang);
 //  dd($companyName);
        foreach ($companiesListWithIds as $id => $city) {

            if ($city == $companyName) {

                return $id;
            }
        }

    }

    public function getCompanyAddresses($companyName, $cityName,$lang)
    {
        $companyId = $this->getCompanyIdByCompanyName($companyName, $cityName,$lang);
        $addressList = $this->getAddressList($companyId,$lang);

        $resultAddressList = [];
        foreach ($addressList as $address) {
            $address = (array)$address;
            $resultAddressList[][] = $address["title"];

        }
        return $resultAddressList;

    }
    public function getCompanyAddressesHowArray($companyName, $cityName,$lang="en")
    {
        $companyId = $this->getCompanyIdByCompanyName($companyName, $cityName,$lang);
        $addressList = $this->getAddressList($companyId,$lang);
        $resultAddressList = [];
        foreach ($addressList as $address) {
            $address = (array)$address;
            $resultAddressList[] = $address["title"];

        }
        return $resultAddressList;

    }

    public function getInfoAboutCompany($companyId,$lang)
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
                    'Api-Lang' => $lang

                ],
                'query' => [
                    'v' => '2.0.0',
                ],
            ]
        );
        $body = $response->getBody();
        $body = (array)json_decode($body);
        Cache::put("bbbb",$body);
        return $body;

    }
    public function getAddressList($companyId,$lang){

        return ($this->getInfoAboutCompany($companyId,$lang))["addresses"];
    }

    public function getAddressIdByAddressName($addressName, $companyName, $cityName,$lang)
    {
        $companyId = $this->getCompanyIdByCompanyName($companyName, $cityName,$lang);
        $addressList = $this->getAddressList($companyId,$lang);

        foreach ($addressList as $address) {
            $address = (array)$address;

            if ($address["title"] == $addressName) {

                return $address['id'];
            }

        }
    }

    public function getScheduleListForCompanyByName(string $nameCompany,string $cityName,$lang){
       $companyId= $this->getCompanyIdByCompanyName($nameCompany,$cityName,$lang);
       $info=$this->getInfoAboutCompany($companyId,$lang);
       //Cache::put("info2",$info["schedule"][0]->id);
       return $info["schedule"];
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
