<?php

namespace App\Services\DotsApi;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class RequestsAboutCompanyItems
{
    private $requestsAboutCompanies;
    private $client;
//    private $lang = ["ua" => "ua", "ru" => "ru", "en" => "en"];
  //  private  $currentLang = "en";
private $requestsAboutCities;
    public function __construct(
        RequestsAboutCompanies $requestsAboutCompanies,
        RequestsAboutCities $requestsAboutCities

    )
    {
        $this->requestsAboutCompanies = $requestsAboutCompanies;
        $this->client = new Client();
        $this->requestsAboutCities=$requestsAboutCities;
    }


    private function getItemsList(string $companyId)
    {

        $response = $this->client->get(
            "https://clients-api.dots.live/api/v2/companies/$companyId/items-by-categories?v=2.0.0",
            [
                'headers' => [
                    'Api-Token' => config("configPermission")["Api_Token"],
                    'Api-Account-Token' => config("configPermission")["Api_Account_Token"],
                    'Content-Type' => config("configPermission")["Content_Type"],
                    'Accept' => config("configPermission")["Accept"],
                    "Api-Lang" => "ua"

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

    public function getItemsListAsArrayByCompanyId(string $companyId)
    {


        return $this->getItemsList($companyId);
    }

    public function getItemsNameList(string $companyId)
    {
        $categoriesListWithItems = $this->getItemsList($companyId);
        $itemList = [];
        foreach ($categoriesListWithItems as $category) {
            $category = (array)$category;
            foreach ($category['items'] as $item) {
                $item = (array)$item;
                $itemList[$item["id"]] = $item["name"];
            }


        }
        return $itemList;
    }

    public function getNamesOfCategoriesAsArrayOfArrays(string $companyId)
    {
        Cache::put("lan12","fs");
        $categoriesListWithItems = $this->getItemsList($companyId);
        $categories = [];
        foreach ($categoriesListWithItems as $category) {
            $category = (array)$category;
            $categories[][] = $category["name"] != "" ? $category["name"] : $category["url"];

        }
        return $categories;
    }

    public function getNamesOfCategories(string $companyId)
    {
        $categoriesListWithItems = $this->getItemsList($companyId);
        $categories = [];
        foreach ($categoriesListWithItems as $category) {
            $category = (array)$category;
            $categories[] = $category["name"]=="" ? $category["url"] : $category["name"];

        }
        return $categories;
    }

    public function getNameItemByIdItem($cityName,$companyName, $itemId)
    {

        Cache::put("cityName1",$cityName);
        Cache::put("companyName1",$companyName);
        Cache::put("itemId1",$itemId);
        $cityId = $this->requestsAboutCompanies->getCityId($cityName);
        Cache::put("cityId1",$cityId);
        $companyList=  $this->requestsAboutCompanies->getCompanyListAsArrayWithId($cityName);
        Cache::put("companyList1",$companyList);
        $companyId="";
        foreach ($companyList as $key=>$company){
            if($company== $companyName  ){
                $companyId=$key;
            }
        }
        Cache::put("companyId1",$companyId);
        $itemList=$this->getItemsListAsArrayByCompanyId($companyId);
        foreach ($itemList as $category){
            Cache::put("category1",$category);
            foreach ($category->items as $item){
                Cache::put("itemmm1",$item);
                if($item->id == $itemId){
                    if($item->name == ""){
                        return $item->url;
                    }
                    return $item->name;
                }

            }

        }
return false;

}

}
