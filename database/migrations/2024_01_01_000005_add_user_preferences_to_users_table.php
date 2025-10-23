<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для добавления пользовательских настроек в таблицу users
 *
 * Добавляет поля для хранения персональных предпочтений пользователей:
 * тема оформления, состояние боковой панели, настройки уведомлений.
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - добавить поля пользовательских настроек
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('theme_preference')->default('light')->after('is_active'); // Предпочтение темы
            $table->boolean('sidebar_collapsed')->default(false)->after('theme_preference'); // Состояние боковой панели
            $table->boolean('notifications_enabled')->default(true)->after('sidebar_collapsed'); // Включены ли уведомления
        });
    }

    /**
     * Откатить миграцию - удалить поля пользовательских настроек
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['theme_preference', 'sidebar_collapsed', 'notifications_enabled']);
        });
    }
};
