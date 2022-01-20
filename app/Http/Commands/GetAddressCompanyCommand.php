<?php

namespace App\Http\Commands;

use App\Services\Telegram\Sender\GetCompanyAddressForOrder;
use Telegram\Bot\Commands\Command;

class GetAddressCompanyCommand extends Command
{
    private $getCompanyAddressForOrder;
    public function __construct()
    {
        $this->getCompanyAddressForOrder=new GetCompanyAddressForOrder();
    }

    protected $name = "company_address";


    protected $description = "Choose address company where you will be take your order";
    public function handle()
    {
        $this->getCompanyAddressForOrder->sendAddresses($this->update["message"]["chat"]["id"]);
        $this->replyWithMessage([
            'chat_id' => $this->update["message"]["chat"]["id"],
            'text'=>"Address was saved",

        ]);

    }
}
