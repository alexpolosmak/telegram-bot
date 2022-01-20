<?php

namespace App\Providers;

use App\Services\DotsApi\RequestsAboutCompanyItems;
use App\Services\Listeners\MainListener;
use App\Services\Telegram\Handlers\CityHandler;
use App\Services\Users\UserService;
use App\Services\Users\UserServiceInterface;
use App\Telegram\BotInstance;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserServiceInterface::class,UserService::class);
        app()->singleton(BotInstance::class,function(){
            return new BotInstance();

        });
        app()->bind(Client::class);



    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }
}
