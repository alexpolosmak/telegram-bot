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


    private function getCitiesList()
    {
        $response = $this->client->get(
            'https://clients-api.dots.live/api/v2/cities',
            [
                'headers' => [
                    'Api-Token' => config("configPermission")["Api_Token"],
                    'Api-Account-Token' => config("configPermission")["Api_Account_Token"],
                    'Content-Type' => config("configPermission")["Content_Type"],
                    'Accept' => config("configPermission")["Accept"]

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

    public function getCitiesListAsArray()
    {
        $body = $this->getCitiesList();
        $citiesList = [];
        foreach ($body as $city) {
            $city = (array)$city;
            $citiesList[] = ($city["name"] == "" ? $city["url"] : $city["name"]);
        }
        return $citiesList;
    }

    public function getCitiesListAsArrayWithId()
    {
        $body = $this->getCitiesList();
        $citiesList = [];
        foreach ($body as $city) {
            $city = (array)$city;
            $citiesList[$city["id"]] = ($city["name"] == "" ? $city["url"] : $city["name"]);
        }
        return $citiesList;
    }

    public function getCitiesListAsArrayOfArrays()
    {
        $body = $this->getCitiesList();
        $citiesList = [];
        foreach ($body as $city) {
            $city = (array)$city;
            $citiesList[] = (array)($city["name"] == "" ? $city["url"] : $city["name"]);
        }
        return $citiesList;
    }
    public function getCityIdByCitName($cityName){
       $cities= $this->getCitiesListAsArrayWithId();
       foreach ($cities as $id=>$city){
           if($city==$cityName){
               return $id;
           }
       }


    }


}
