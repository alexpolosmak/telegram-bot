<?php

namespace App\Services\Telegram\Handlers;







use App\Telegram\BotInstance;
use Illuminate\Support\Facades\App;

class CityHandler
{
    private $bot;

     public  function __construct( )
    {
        $bot= new BotInstance();
        $this->bot=$bot->getBot();

    }

    public  function getCity()
    {
//dd($this->getCityFromUpdateData());

    }

    private function getCityFromUpdateData(){
        $updates=$this->bot->getUpdates();

        for ($i=count($updates)-1;$i>0;$i--){
            if($updates[$i]["message"]["text"]=="/city"){
               return $updates[$i-1]["message"];
            }
        }

    }


}
