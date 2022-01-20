<?php

namespace App\Http\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class StartCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "start";

    /**
     * @var string Command Description
     */
    protected $description = "Start Command to get you started";

    /**
     * @inheritdoc
     */
    public function handle()
    {
        $this->replyWithMessage(['text' => 'Hello! Welcome to our bot, Here are our available commands:']);
        $this->replyWithMessage([
            'parse_mode'  =>"HTML",
            'text' => "How you can make order use this bot?
            1.Share your contact.
            2.Choose city where you want get order.
            3.Choose company where you want make order.
            4.Press button with dish name for adding dish in your order.
            5.Choose address company where you want get order
            If you want choose several identical dishes just press dish button  several.
              Remember!!!
              You can only choose dishes from one company.
              If you make mistake in the making your order just repeat this guide again."
        ]);
        $this->replyWithChatAction(['action' => Actions::TYPING]);
        $commands = $this->getTelegram()->getCommands();

        $response = '';
        foreach ($commands as $name => $command) {
            $response .= sprintf('/%s - %s' . PHP_EOL, $name, $command->getDescription());
        }

        $this->replyWithMessage(['text' => $response]);
        $this->triggerCommand("contact");
    }
}
