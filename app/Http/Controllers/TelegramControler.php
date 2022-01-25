<?php

namespace App\Http\Controllers;

use App\Http\Commands\CreateOrderCommand;
use App\Http\Commands\GetAddressCompanyCommand;
use App\Http\Commands\CityCommand;
use App\Http\Commands\GetContactCommand;
use App\Http\Commands\SetLanguage;
use App\Http\Commands\StartCommand;
use App\Jobs\ProcessingTextMessageJob;
use App\Services\Listeners\MainListener;
use App\Telegram\BotInstance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

class TelegramControler extends Controller
{

private $bot;

    public function __construct(BotInstance $bot )
    {
        $this->bot = $bot->getBot();

    }

    /**
     * @throws TelegramSDKException
     *
     *
     */
    public function update()
    {
        $this->bot->addCommand(StartCommand::class);
        $this->bot->addCommand(SetLanguage::class);
        $this->bot->addCommand(GetContactCommand::class);
        $this->bot->addCommand(CityCommand::class);

        $this->bot->addCommand(CreateOrderCommand::class);
        $this->bot->addCommand(GetAddressCompanyCommand::class);

        $this->bot->commandsHandler(true);

       ProcessingTextMessageJob::dispatch($this->bot->getWebhookUpdate())->onQueue("default");
        return response()->json(null, 200);


    }

}
