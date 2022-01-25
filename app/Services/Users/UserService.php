<?php

namespace App\Services\Users;

use App\Models\User;
use App\Services\Users\Handlers\UserExistHandler;
use App\Services\Users\Handlers;
use App\Services\Users\Handlers\UserStoreHandler;
use App\Services\Users\Repositories\UserRepository;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
        Cache::put("not_exist", $data);
        if (!($this->exist($data))) {
            Cache::put("exist", $data);
          //  Log::info("i am in exist", ["exist"]);
            return $this->userStoreHandler->handle($data);
        }

        return $this->clearCart($data);


    }

    public function find($data)
    {
        // TODO: Implement find() method.
    }

    public function exist($data)
    {
        $chat_id = $this->userExistHandler->handle($data);
        Cache::put("chat_id", $data);
        return $this->userRepository->find($chat_id);
    }

    public function clearCart($data)
    {
        $chat_id = $this->userExistHandler->handle($data);
        $user = User::getUser($chat_id);
//dd($user);

        return $this->userRepository->CleanCartUser($user["cart_id"]);
    }
}
