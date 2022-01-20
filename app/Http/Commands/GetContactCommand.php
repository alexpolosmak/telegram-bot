<?php

namespace App\Http\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Button;
use Telegram\Bot\Keyboard\Keyboard;

class GetContactCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "contact";

    /**
     * @var string Command Description
     */
    protected $description = "Share you contact please";

    /**
     * @inheritdoc
     */


    public function handle()
    {

        $button=Button::make([
            "text"=>"share contact",
            "request_contact"=>true
        ]);
        $keyboard=[
            [$button]
        ];


        $reply_markup = Keyboard::button([
            'keyboard' =>$keyboard ,
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
            "selective"=>false

        ]);

        $response = $this->replyWithMessage([
            'chat_id' => $this->update["message"]["chat"]["id"],
            'text' => 'Share own contact',
            'reply_markup' => $reply_markup
        ]);
    }
}