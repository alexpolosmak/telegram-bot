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

    public function __construct(
        RequestsAboutCompanies $requestsAboutCompanies

    )
    {
        $this->requestsAboutCompanies = $requestsAboutCompanies;
        $this->client = new Client();
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
      //  $this->setCurrentLanguage($companyId);

        return $this->getItemsList($companyId);
    }

    public function getItemsNameList(string $companyId)
    {
     //   $this->setCurrentLanguage($companyId);
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
      //  $this->setCurrentLanguage($companyId);
       // Cache::put("lan1",$this->currentLang);
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
      //  $this->setCurrentLanguage($companyId);
        //Cache::put("lan12",$this->currentLang);
        $categoriesListWithItems = $this->getItemsList($companyId);
        $categories = [];
        foreach ($categoriesListWithItems as $category) {
            $category = (array)$category;
            $categories[] = $category["name"]=="" ? $category["url"] : $category["name"];

        }
        return $categories;
    }

//    private function setCurrentLanguage(string $companyId) : void
//    {
//        foreach ($this->lang as $language) {
//            if (!$this->mustSwitchRequestLanguage($companyId)){
//                $this->currentLang=$language;
//                return;
//            }
//
//            }
//       // $this->currentLang="en";
//
//    }

//    private function mustSwitchRequestLanguage(string $companyId) :bool
//    {
//        $data= $this->getItemsList( $companyId);
//        foreach ($data as $category){
//
//            if($category->name=="" || $category->url=="" ) {
//
//
//                return true;
//            }
//            foreach ($category->items as $item){
//                if($item->name=="")
//                    return true;
//           }
//
//        }
//        Cache::put("ifif1","true".$this->currentLang);
//        return false;
//    }

}
