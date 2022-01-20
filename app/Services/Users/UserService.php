<?php

namespace App\Services\Users;

use App\Models\User;
use App\Services\Users\Handlers\UserExistHandler;
use App\Services\Users\Handlers;
use App\Services\Users\Handlers\UserStoreHandler;
use App\Services\Users\Repositories\UserRepository;

class UserService implements UserServiceInterface
{
    private $userRepository;
    private $userStoreHandler;
    private $userExistHandler;

    public function __construct(
        UserStoreHandler $userStoreHandler,
        UserRepository   $userRepository,
        UserExistHandler $userExistHandler
    )
    {
        $this->userRepository = $userRepository;
        $this->userStoreHandler = $userStoreHandler;
        $this->userExistHandler = $userExistHandler;

    }


    public function store($data)
    {

        if (!($this->exist($data))) {
            $this->userStoreHandler->handle($data);
        }

        $this->clearCart($data);


    }

    public function find($data)
    {
        // TODO: Implement find() method.
    }

    public function exist($data)
    {
        $chat_id = $this->userExistHandler->handle($data);
        return $this->userRepository->find($chat_id);
    }

    public function clearCart($data)
    {
        $chat_id = $this->userExistHandler->handle($data);
        $user = User::getUser($chat_id);
//dd($user);

        $this->userRepository->CleanCartUser($user[0]["cart_id"]);
    }
}
