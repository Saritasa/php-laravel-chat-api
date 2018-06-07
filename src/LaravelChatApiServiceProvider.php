<?php

namespace Saritasa\LaravelChatApi;

use Illuminate\Support\ServiceProvider;
use Saritasa\LaravelChatApi\Contracts\IChatService;
use Saritasa\LaravelChatApi\Services\ChatService;

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
            'laravel_chat_api'
        );
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel_chat_api.php', 'laravel_chat_api');
    }

    /**
     * Register bindings.
     */
    public function registerBindings(): void
    {
        $this->app->bind(IChatService::class, ChatService::class);
    }
}
