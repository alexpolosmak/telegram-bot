<?php

namespace App\Services\Telegram\Sender;

use App\Models\User;
use App\Services\DotsApi\RequestsAboutCompanies;
use App\Telegram\BotInstance;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;


class RequestDeliveryTimeSender
{
    private $bot;
    private $requestsAboutCompanies;
    private  $nextDaySchedule ;
    private $actionalDay;

    public function __construct(
        BotInstance            $bot,
        RequestsAboutCompanies $requestsAboutCompanies

    )
    {
        $this->bot = $bot->getBot();
        $this->requestsAboutCompanies = $requestsAboutCompanies;
    }

    public function sendRequestAboutDeliveryTime($user, $cartUser)
    {
        date_default_timezone_set('Europe/Kiev');
        $this->bot->sendChatAction([
            'chat_id' => $user["chat_id"],
            "action" => "typing"
        ]);

        App::setLocale($user["lang"]);

        $schedule = $this->requestsAboutCompanies->getScheduleListForCompanyByName($cartUser["company"], $cartUser["town"], $user["lang"]);
        Cache::put("chr", $schedule);
        $this->actionalDayOfWeekday = date("N");
        $daysWhereCompanyNotWorks = [];
        foreach ($schedule as $day) {

            if ($day->endTime > time() && $day->startTime < time() && $day->isActive == true) {


               // $this->getInfoAboutNextDay($day);

                Cache::put("rhc", $schedule);
                $text = __("message.deliveryTime") . $day->start . __("message.to") . $day->end;
                return $this->bot->sendMessage([
                    'chat_id' => $user["chat_id"],
                    'text' => $text,
                    'parse_mode' => "HTML",
                    "reply_markup" => $this->getButtonItem("name", "name")

                ]);

            }
            if($day->isActive==false){
                $daysWhereCompanyNotWorks[]=$day->id;
            }

        }
        $idNextDay= $this->actionalDayOfWeekday==7 ? 0: $this->actionalDayOfWeekday;
        $this->nextDaySchedule=$schedule[$idNextDay];
        $this->companyNotWorkingToday($daysWhereCompanyNotWorks, $user["chat_id"]);


    }

    private function getButtonItem()
    {//доработать
        date_default_timezone_set('Europe/Kiev');
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => __("message.in_one_hour"), 'callback_data' => time() + (60 * 3) + 3600]
                ],
                [
                    ['text' => __("message.in_two_hours"), 'callback_data' => time() + 60 * 3 + 3600 * 2]
                ],
                [
                    ['text' => __("message.in_tree_hours"), 'callback_data' => time() + 60 * 3 + 3600 * 3]
                ],
                [
                    ['text' => __("message.in_four_hours"), 'callback_data' => time() + 60 * 3 + 3600 * 4]
                ],

                [
                    ['text' => __("message.f_a_c"), 'callback_data' => time() + 60 * 3]
                ],

            ]
        ];
        return json_encode($keyboard);

    }

    private function companyNotWorkingToday( $daysWhereCompanyNowWorks, $chatId)
    {
        Cache::put("nextday", $this->nextDaySchedule);
        $daysOfWeek = "";
        foreach ($daysWhereCompanyNowWorks as $dayId) {
            if ($dayId == 0) {
                $daysOfWeek .= __("message.monday") . ", ";
            }
            if ($dayId == 1) {
                $daysOfWeek .= __("message.tuesday") . ", ";
            }
            if ($dayId == 2) {
                $daysOfWeek .= __("message.wednesday") . ", ";
            }
            if ($dayId == 3) {
                $daysOfWeek .= __("message.thursday") . ", ";
            }
            if ($dayId == 4) {
                $daysOfWeek .= __("message.friday") . ", ";
            }
            if ($dayId == 5) {
                $daysOfWeek .= __("message.saturday") . ", ";
            }
            if ($dayId == 6) {
                $daysOfWeek .= __("message.sunday") . ", ";
            }

        }
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => __("message.own_delivery_time"), 'callback_data' => "sdfasdfsdf_as2f_jfao4_faf"]
                ]
            ]
        ];
        return $this->bot->sendMessage([
            'chat_id' => $chatId,
            'text' => __("message.company_not_working_today") . __("message.next_day_schedule_") .
                $this->nextDaySchedule->start . __("message.to") .   $this->nextDaySchedule->end .".\n". __("message.days_where_company_not_works") .
                $daysOfWeek,

//            'parse_mode' => "HTML",
//            "reply_markup" => json_encode($keyboard)


        ]);

    }

}
