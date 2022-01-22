<?php

namespace App\Services\Users\Handlers;

use App\Services\Users\Repositories\UserRepository;
use Illuminate\Support\Facades\Cache;

class UserExistHandler
{


    public function handle($message)
    {
        Cache::put("handle", $message["user_id"]);
        return $message["user_id"];
    }

}
