<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Bot\Manager;

class BotServiceProvider extends ServiceProvider
{

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Bot\Manager', Manager::class);
    }

    public function provides()
    {
        return ['bot'];
    }


}
