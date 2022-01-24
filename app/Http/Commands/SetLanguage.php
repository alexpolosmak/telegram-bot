<?php

namespace App\Http\Commands;

use App\Models\User;
use App\Services\DotsApi\RequestsAboutCities;
use Illuminate\Support\Facades\App;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Objects\BotCommand;

class SetLanguage extends Command
{

    private $apiCompanyServices;

    public function __construct()
    {

        $this->apiCompanyServices = new RequestsAboutCities();
    }

    /**
     * @var string Command Name
     */
    protected $name = "language";

    /**
     * @var string Command Description
     */
    protected $description = "Set language";

    /**
     * @inheritdoc
     */
    public function handle()
    {
        $user = User::getUser($this->update["message"]["chat"]["id"]);
        App::setLocale($user["lang"]);


        $languages = [
            ["english"],
            ["ukrainian"],
            ["russian"]
        ];


        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $reply_markup = Keyboard::button([
            'keyboard' => $languages,
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
            "selective" => false,
            "remove_keyboard" => true


        ]);

        $response = $this->replyWithMessage([
            'chat_id' => $this->update["message"]["chat"]["id"],
            'text' => __("message.language"),
            'reply_markup' => $reply_markup
        ]);
      //  $this->triggerCommand("contact");

    }
}
