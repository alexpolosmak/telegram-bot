<?php

namespace App\Services\Convertors;

use Ramsey\Uuid\Type\Time;

class TimeConvertor
{
    public function convertTimeFromTimeDeliveryToUnix($timeDelivery)
    {

        date_default_timezone_set('Europe/Kiev');

        $items = explode(" ", $timeDelivery);
        if ($items[count($items) - 1] == "хв." || 'хв' || "min" || "min.") {
            return time() + (($items[0]+1) * 60);
        }

    }

}
