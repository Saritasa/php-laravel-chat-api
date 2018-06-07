<?php

namespace Saritasa\Laravel\Chat;

use Illuminate\Support\ServiceProvider;

class LaravelChatApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes(
            [__DIR__ . '/../migrations' => database_path('migrations'),],
            'laravel-chat-api-migrations'
        );

        $this->publishes(
            [
                __DIR__ . '/../config/laravel_chat_api.php' =>
                    $this->app->make('path.config') . DIRECTORY_SEPARATOR . 'laravel_chat_api.php',
            ],
            'laravelChatApi'
        );
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel_chat_api.php', 'laravelChatApi');
    }

}
