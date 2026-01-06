<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

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
        // Настройка планировщика задач
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            
            // Ежедневное резервное копирование БД в 2:00 ночи
            $schedule->command('db:backup')
                ->dailyAt('02:00')
                ->withoutOverlapping()
                ->runInBackground()
                ->onFailure(function () {
                    \Illuminate\Support\Facades\Log::error('Ошибка автоматического резервного копирования БД');
                });
            
            // Еженедельное полное резервное копирование в воскресенье в 3:00
            $schedule->command('db:backup --keep=90')
                ->weeklyOn(0, '03:00')
                ->withoutOverlapping()
                ->runInBackground()
                ->onFailure(function () {
                    \Illuminate\Support\Facades\Log::error('Ошибка еженедельного резервного копирования БД');
                });
        });
    }
}
