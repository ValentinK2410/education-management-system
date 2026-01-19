<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для добавления поля users_per_page в таблицу users
 * 
 * Это поле хранит настройку пользователя о количестве элементов на странице списка пользователей
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - добавить поле users_per_page
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('users_per_page')->default(50)->after('moodle_token');
        });
    }

    /**
     * Откатить миграцию - удалить поле users_per_page
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('users_per_page');
        });
    }
};
