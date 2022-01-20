<?php

namespace App\Services\Users\Handlers;

use App\Services\Users\Repositories\UserRepository;

class UserExistHandler
{


    public function handle($message)
    {
        return $message["message"]["contact"]["user_id"];
    }

}
