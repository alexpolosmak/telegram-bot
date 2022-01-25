<?php

namespace App\Http\Commands;

use App\Models\User;
use App\Services\Telegram\Sender\GetCompanyAddressForOrder;
use Illuminate\Support\Facades\App;
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
        $user = User::getUser($this->update["message"]["chat"]["id"]);
        if ($user != []) {
            App::setLocale($user["lang"]);
        } else {
            return;
        }
        $this->getCompanyAddressForOrder->sendAddresses($this->update["message"]["chat"]["id"]);
    }
}
