<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kerox\Messenger\Messenger;

class MessengerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Messenger::class, function($app) {
            return new Messenger(
                $app->make('config')->get('api.fb_app_secret'),
                $app->make('config')->get('api.fb_verify_token'),
                $app->make('config')->get('api.fb_page_token')
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['messenger'];
    }
}
