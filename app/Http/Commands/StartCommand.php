<?php

namespace App\Http\Commands;

use App\Models\User;
use Illuminate\Support\Facades\App;
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
        $user = User::getUser($this->update["message"]["chat"]["id"]);
        if($user!=[])
        App::setLocale($user["lang"]);
        $this->replyWithMessage(['text' => __("message.start_message_greeting")]);
        $this->replyWithMessage([
            'parse_mode' => "HTML",
            'text' =>__("message.start_message")
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
