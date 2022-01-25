<?php

namespace App\Http\Commands;

use App\Services\Telegram\Sender\GetCompanyAddressForOrder;
use Telegram\Bot\Commands\Command;

class SetDeliveryTimeCommand extends Command
{
    private $getCompanyAddressForOrder;
    public function __construct()
    {
        $this->getCompanyAddressForOrder=new GetCompanyAddressForOrder();
    }

    protected $name = "company_address";


    protected $description = "Sets delivery time";
    public function handle()
    {
        $this->getCompanyAddressForOrder->sendAddresses($this->update["message"]["chat"]["id"]);

    }
}
