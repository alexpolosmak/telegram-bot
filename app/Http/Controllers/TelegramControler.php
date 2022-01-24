<?php

namespace App\Http\Controllers;

use App\Http\Commands\CreateOrderCommand;
use App\Http\Commands\GetAddressCompanyCommand;
use App\Http\Commands\CityCommand;
use App\Http\Commands\GetContactCommand;
use App\Http\Commands\SetLanguage;
use App\Http\Commands\StartCommand;
use App\Services\Listeners\MainListener;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

class TelegramControler extends Controller
{
    private $mainListener;

    public function __construct(MainListener $mainListener)
    {
        $this->mainListener = $mainListener;
    }

    /**
     * @throws TelegramSDKException
     *
     *
     */
    public function update(Request $request)
    {

        $telegram = new Api("5052566047:AAHiqDUingQ8UmjqlRgAbyAsg-V4Trzxqow");

        $telegram->addCommand(StartCommand::class);
        $telegram->addCommand(SetLanguage::class);
        $telegram->addCommand(GetContactCommand::class);
        $telegram->addCommand(CityCommand::class);

    $telegram->addCommand(CreateOrderCommand::class);
        $telegram->addCommand(GetAddressCompanyCommand::class);

        $telegram->commandsHandler(true);
        Cache::put("listen1", "I will go to listen1");
      $this->mainListener->listen();
        return response()->json(null, 200);


    }

//    public function getUpdate()
//    {
//        $telegram = new Api("5052566047:AAHiqDUingQ8UmjqlRgAbyAsg-V4Trzxqow");
//        $telegram->getUpdates();
//
//    }
}
