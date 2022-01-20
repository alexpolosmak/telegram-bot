<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public $timestamps = false;
    protected $fillable = ["name", "chat_id", "cart_id", "number_phone"];


    public function getCartUser($chatId)
    {
        $user = (User::getUser($chatId))[0];

        return Cache::get($user["cart_id"]);
    }

    public function getUser($chatId): array
    {
        return (User::query()->where("chat_id", "=", $chatId)->get())->toArray();
    }
}
