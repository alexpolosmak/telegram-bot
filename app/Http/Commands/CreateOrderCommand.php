<?php

namespace App\Http\Commands;

use App\Models\User;
use App\Services\DotsApi\CreateOrderRequest;
use App\Services\DotsApi\RequestsAboutCities;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Commands\Command;

class CreateOrderCommand extends  Command
{

    protected $name = "create_order";


    protected $description = "Choose this if you want create order";


    private $createOrderRequest;
    public function __construct()
    {
        $this->createOrderRequest=new CreateOrderRequest();

    }

    public function handle()
    {
        $user = User::getUser($this->update["message"]["chat"]["id"]);
        if ($user != []) {
            App::setLocale($user["lang"]);
        } else {
            return;
        }
        $message=$this->update->toArray();
        $this->createOrderRequest->createOrder($message["message"]["chat"]["id"]);
        $this->replyWithMessage([
            'chat_id' => $message["message"]["chat"]["id"],
            'text'=>__("message.create_is_successful"),

        ]);
    }

}
