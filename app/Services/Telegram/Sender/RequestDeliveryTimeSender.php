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
        $this->bot->sendChatAction([
            'chat_id' => $user["chat_id"],
            "action" => "typing"
        ]);

        App::setLocale($user["lang"]);

        $schedule = $this->requestsAboutCompanies->getScheduleListForCompanyByName($cartUser["company"], $cartUser["town"]);
        foreach ($schedule as $day) {
            if ($day->endTime > time() && $day->startTime < time() && $day->isActive == true) {
                Cache::put("forif", "true");
                $text = __("message.deliveryTime") . $day->start . __("message.to") . $day->end;
                return $this->bot->sendMessage([
                    'chat_id' => $user["chat_id"],
                    'text' => $text,
                    'parse_mode' => "HTML"

                ]);

            }

        }
        return $this->bot->sendMessage([
            'chat_id' => $user["chat_id"],
            'text' => __("message.company_not_working_today"),
            'parse_mode' => "HTML"

        ]);


    }
}
