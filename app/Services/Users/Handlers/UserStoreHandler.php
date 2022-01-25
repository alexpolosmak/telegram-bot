<?php

namespace App\Services\Users\Handlers;

use App\Services\Users\Repositories\UserRepository;
use Illuminate\Support\Facades\Cache;


class UserStoreHandler
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle($message)
    {
        Cache::put("inHandler",$message);
       return $this->userRepository->store( $message);
    }

}
