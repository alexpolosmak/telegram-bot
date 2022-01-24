<?php

namespace App\Http\Commands;

use App\Languages\English;

use App\Models\User;
use App\Services\DotsApi\RequestsAboutCities;
use App\Services\DotsApi\RequestsAboutCompanyItems;
use App\Services\Telegram\Handlers\CityHandler;
use App\Telegram\BotInstance;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;
use function PHPUnit\Framework\logicalAnd;

class CityCommand extends Command
{
    private $apiCompanyServices;

    public function __construct()
    {

        $this->apiCompanyServices = new RequestsAboutCities();

    }

    /**
     * @var string Command Name
     */
    protected $name = "city";

    /**
     * @var string Command Description
     */
    protected $description = "Set city in your cart";

    /**
     * @inheritdoc
     */
    public function handle()
    {

        $user = User::getUser($this->update["message"]["chat"]["id"]);
        App::setLocale($user["lang"]);
        $citiesList = $this->apiCompanyServices->getCitiesListAsArrayOfArrays();


        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $reply_markup = Keyboard::button([
            'keyboard' => $citiesList,
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
            "selective" => false,
            "remove_keyboard" => true


        ]);


        //
        $response = $this->replyWithMessage([
            'chat_id' => $user["chat_id"],
            'text' => __("message.enter_city"),
            'reply_markup' => $reply_markup
        ]);

    }
}
