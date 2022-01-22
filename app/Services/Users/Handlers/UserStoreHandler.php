<?php

namespace App\Services\Users\Handlers;

use App\Services\Users\Repositories\UserRepository;

class UserStoreHandler
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle($message)
    {
       return $this->userRepository->store( $message);
    }

}
