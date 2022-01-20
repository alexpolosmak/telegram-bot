<?php

namespace App\Services\DotsApi;


use GuzzleHttp\Client;

class RequestsAboutCompanyItems
{
    private $requestsAboutCompanies;
    private $client;

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
        return $this->getItemsList($companyId);

        // dd($body);
//        dd( $body);
//        $itemList = [];
//        foreach ($body as $item) {
//            $item = (array)$item;
//            $itemList[$item["id"]] = ($item["name"] == "" ? $item["url"] : $item["name"]);
//
//        }
//        return $itemList;
    }

//    public function getItemsListByCompanyAsArraysOfArray(string $company)
//    {
//        $body = $this->getItemsList($company);
//        $companyList = [];
//        foreach ($body as $company) {
//            $company = (array)$company;
//            $companyList[] = (array)$company["name"] ;
//        }
//        return $companyList;
//    }
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

    public function getNamesOfCategoriesAsArrayOfArrays(string $companyId){
        $categoriesListWithItems = $this->getItemsList($companyId);
        $categories = [];
        foreach ($categoriesListWithItems as $category) {
            $category = (array)$category;
            $categories[][]=$category["name"];

        }
        return $categories;
    }

    public function getNamesOfCategories(string $companyId){
        $categoriesListWithItems = $this->getItemsList($companyId);
        $categories = [];
        foreach ($categoriesListWithItems as $category) {
            $category = (array)$category;
            $categories[]=$category["name"];

        }
        return $categories;
    }


}
