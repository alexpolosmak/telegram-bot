<?php

namespace App\Services\Users;

use Telegram\Bot\Objects\Update;

interface UserServiceInterface
{
    public function store( $data);
    public function find( $data);
    public function exist( $data);

}
