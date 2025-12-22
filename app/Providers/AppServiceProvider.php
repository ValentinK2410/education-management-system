<?php

namespace App\Providers;

use App\Events\UserCreated;
use App\Listeners\SyncUserToMoodle;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Регистрируем слушатель для синхронизации пользователей с Moodle
        Event::listen(
            UserCreated::class,
            SyncUserToMoodle::class
        );
    }
}
