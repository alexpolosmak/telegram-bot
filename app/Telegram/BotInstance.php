<?php

namespace App\Telegram;

use Telegram\Bot\Api;

class BotInstance
{
    private $bot;

    public function __construct()
    {
      //  $this->bot = new Api("5204387457:AAECdrU22k486Trbizm2vmUD3Th_nxEkyYg");
        $this->bot = new Api("5052566047:AAHiqDUingQ8UmjqlRgAbyAsg-V4Trzxqow");
    }

    public function getBot()
    {
        return $this->bot;
    }

}
