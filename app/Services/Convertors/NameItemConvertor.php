<?php

namespace App\Services\Convertors;
class NameItemConvertor
{
    public function controlItemNameLength($name)
    {
        $name = explode(" ", $name);
        if (count($name) == 1) {
            return (string)$name[0];
        }
        return $name[0] . " " . $name[1];
    }

}
