<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Bot\Messenger\MessengerClient;
use Kerox\Messenger\Messenger;

class MessengerBotServiceProvider extends ServiceProvider
{

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Messenger\MessengerBot', MessengerClient::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['messenger.bot'];
    }
}
