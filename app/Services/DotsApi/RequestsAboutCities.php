<?php

namespace App\Services\DotsApi;


use GuzzleHttp\Client;

class RequestsAboutCities
{
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }


    private function getCitiesList(string $lang="en")
    {
        $response = $this->client->get(
            'https://clients-api.dots.live/api/v2/cities',
            [
                'headers' => [
                    'Api-Token' => config("configPermission")["Api_Token"],
                    'Api-Account-Token' => config("configPermission")["Api_Account_Token"],
                    'Content-Type' => config("configPermission")["Content_Type"],
                    'Accept' => config("configPermission")["Accept"],
                    "Api-Lang"=>$lang

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

    public function getCitiesListAsArray(string $lang="en")
    {
        $body = $this->getCitiesList($lang);
        $citiesList = [];
        foreach ($body as $city) {
            $city = (array)$city;
            $citiesList[] = ($city["name"] == "" ? $city["url"] : $city["name"]);
        }
        return $citiesList;
    }

    public function getCitiesListAsArrayWithId(string $lang="en")
    {
        $body = $this->getCitiesList($lang);
        $citiesList = [];
        foreach ($body as $city) {
            $city = (array)$city;
            $citiesList[$city["id"]] = ($city["name"] == "" ? $city["url"] : $city["name"]);
        }
        return $citiesList;
    }

    public function getCitiesListAsArrayOfArrays(string $lang="en")
    {
        $body = $this->getCitiesList($lang);
        $citiesList = [];
        foreach ($body as $city) {
            $city = (array)$city;
            $citiesList[] = (array)($city["name"] == "" ? $city["url"] : $city["name"]);
        }
        return $citiesList;
    }
    public function getCityIdByCitName($cityName,$lang){
       $cities= $this->getCitiesListAsArrayWithId($lang);
       foreach ($cities as $id=>$city){
           if($city==$cityName){
               return $id;
           }
       }


    }


}
