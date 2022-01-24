<?php

namespace App\Services\Convertors;


use Illuminate\Support\Facades\Cache;

class NameItemConvertor
{
    public function controlItemNameLength($name)
    {
        $name = explode(" ", $name);
        if (count($name) == 1) {
            return (string)$name[0];
        }
        if(count($name) >2){
            $str= $name[0] . " " . $name[1]." ".$name[2];

        }
        if(count($name) ==2){
            $str= $name[0] . " " . $name[1];

        }

//        Cache::put("name",$str);
//        Cache::put("ifname1",mb_strlen($str));
        if(mb_strlen($str)>23){
            return $name[0] . " " . $name[1];
        }
        return $str;
    }

}
