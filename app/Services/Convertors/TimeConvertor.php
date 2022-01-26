<?php

namespace App\Services\Convertors;

use Ramsey\Uuid\Type\Time;

class TimeConvertor
{
    public function convertTimeFromTimeDeliveryToUnix($timeDelivery)
    {

        date_default_timezone_set('Europe/Kiev');

        $items = explode(" ", $timeDelivery);
        if ($items[count($items) - 1] == "хв." || 'хв' || "min" || "min." || "мін." || "мін.") {
            return time() + (($items[0]+2  ) * 60);
        }

    }
    public function convertTimeFromHourAndMinutesFormat($timeInHourAndMinutesFormat){
        date_default_timezone_set('Europe/Kiev');
        $timeInHourAndMinutesFormat=trim($timeInHourAndMinutesFormat);
        $timeInHourAndMinutesFormat = explode(":", $timeInHourAndMinutesFormat);
        $year= date("Y",time());
        $month=date("m",time());
        $day=date("j",time());
        return mktime($timeInHourAndMinutesFormat[0],$timeInHourAndMinutesFormat[1],0,$month,$day,$year);

    }

    public function convertTimeFromHourAndMinutesAndDaysFormat($timeInHourAndMinutesAndDaysFormat){
        date_default_timezone_set('Europe/Kiev');
        $timeInHourAndMinutesFormat=trim($timeInHourAndMinutesAndDaysFormat);
        $timeInHourAndMinutesFormat = explode(" ", $timeInHourAndMinutesFormat);

        $hoursAndMinutes=trim($timeInHourAndMinutesFormat[0]);
        $hoursAndMinutes=explode(":",$hoursAndMinutes);

        $daysAndMonth=trim($timeInHourAndMinutesFormat[1]);
        $daysAndMonth=explode(".",$daysAndMonth);

        $date=[
            "hour"=>$hoursAndMinutes[0],
            "minute"=>$hoursAndMinutes[1],
            "day"=>$daysAndMonth[0],
            "month"=>$daysAndMonth[1]

        ];
      //  $timeInHourAndMinutesFormat = explode(":", $timeInHourAndMinutesFormat);
        $year= date("Y",time());
//        $month=date("m",time());
//        $day=date("j",time());
        return mktime($date["hour"],$date["minute"],0,$date["month"],$date["day"],$year);

    }

}
