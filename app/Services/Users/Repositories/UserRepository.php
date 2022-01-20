<?php

namespace App\Services\Users\Repositories;

use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Illuminate\Support\Str;


class UserRepository
{

    public function store(array $data)
    {
        $cartID = Str::random(20);
        $user = User::query()->create([
            "name" => $data["first_name"],
            "chat_id" => (string)$data["user_id"],
            "cart_id" => $cartID,
            "number_phone" => $data["phone_number"],

        ]);
        Cache::put($cartID, [
            "number_phone" => $data["phone_number"],
            "name" => $data["first_name"],
            "town" => "",
            "company" => "",
            "items" => []
        ]);

    }

    public function find(string $username)
    {
        if ((User::query()->where("chat_id", "=", $username)->get())->all() == null) {
            return false;
        }
        return true;
    }

    public function CleanCartUser($cartID)
    {
        $cartUser = Cache::get($cartID);
        $cartUser ["town"] = "";
        $cartUser ["company"] = "";
        $cartUser ["items"] = [];
        if(array_key_exists("addressCompany",$cartUser)){
            $cartUser["addressCompany"]="";
        }
        Cache::put($cartID, $cartUser);
    }


}
