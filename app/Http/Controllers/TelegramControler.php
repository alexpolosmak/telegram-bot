<?php

namespace App\Http\Controllers;

use App\Http\Commands\CreateOrderCommand;
use App\Http\Commands\GetAddressCompanyCommand;
use App\Http\Commands\CityCommand;
use App\Http\Commands\GetContactCommand;
use App\Http\Commands\SelectCompanyCommand;
use App\Http\Commands\SaveCityCommand;
use App\Http\Commands\StartCommand;
use App\Services\Listeners\MainListener;

use http\Message;
use Illuminate\Http\Request;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

class TelegramControler extends Controller
{
    private $mainListener;
    public function __construct(MainListener $mainListener)
    {
        $this->mainListener=$mainListener;
    }

    /**
     * @throws TelegramSDKException
     *
     *
     */
    public function update(Request $request)
    {


        $telegram = new Api("5052566047:AAHiqDUingQ8UmjqlRgAbyAsg-V4Trzxqow");
        dd($request->all());
        $telegram->addCommand(StartCommand::class);
        $telegram->addCommand(GetContactCommand::class);
        $telegram->addCommand(CityCommand::class);
       $telegram->addCommand(CreateOrderCommand::class);
        $telegram->addCommand(GetAddressCompanyCommand::class);
        $telegram->commandsHandler(false);
        $this->mainListener->listen();


//        $response = $telegram->sendMessage([
//            "chat_id"=>924608003,
//            "text"=>"hello i am BotSdk"
//        ]);

//        $botId = $response->getId();
//        $firstName = $response->getFirstName();
//        $username = $response->getUsername();
    }

    public function getUpdate()
    {
        $telegram = new Api("5052566047:AAHiqDUingQ8UmjqlRgAbyAsg-V4Trzxqow");
        // dd( $telegram->getUpdates(["message"],false));
        $telegram->getUpdates();

    }
}
