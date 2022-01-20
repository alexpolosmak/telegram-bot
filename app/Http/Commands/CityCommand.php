<?php

namespace App\Http\Commands;

use App\Services\DotsApi\RequestsAboutCities;
use App\Services\DotsApi\RequestsAboutCompanyItems;
use App\Services\Telegram\Handlers\CityHandler;
use App\Telegram\BotInstance;
use GuzzleHttp\Client;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class CityCommand extends Command
{
    private $apiCompanyServices;

    public function __construct()
    {

        $this->apiCompanyServices = new RequestsAboutCities() ;
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
     //   dd("hello");


        $citiesList = $this->apiCompanyServices->getCitiesListAsArrayOfArrays();
      //  $citiesList[]=(array)"/city";
       // dd($citiesList);
       // dd($citiesList);
//dd($this->cityhandler->getCity());
//dd(is_array($citiesList));


        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $reply_markup = Keyboard::button([
            'keyboard' => $citiesList,
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
            "selective" => false,
            "remove_keyboard" => true


        ]);

        $response = $this->replyWithMessage([
            'chat_id' => $this->update["message"]["chat"]["id"],
            'text'=>"Enter your own city:",
            'reply_markup' => $reply_markup
        ]);
//        $commands = $this->getTelegram()->getCommands();
//
//        $response = '';
//        foreach ($commands as $name => $command) {
//            $response .= sprintf('/%s - %s' . PHP_EOL, $name, $command->getDescription());
//        }
//
//        $this->replyWithMessage(['text' => $response]);
        //  $this->triggerCommand("menu");
    }
}
