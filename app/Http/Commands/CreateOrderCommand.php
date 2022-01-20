<?php

namespace App\Http\Commands;

use App\Services\DotsApi\CreateOrderRequest;
use App\Services\DotsApi\RequestsAboutCities;
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
        $this->createOrderRequest->createOrder($this->update["message"]["chat"]["id"]);
        $this->replyWithMessage([
            'chat_id' => $this->update["message"]["chat"]["id"],
            'text'=>"Created is successful!",

        ]);
    }

}
