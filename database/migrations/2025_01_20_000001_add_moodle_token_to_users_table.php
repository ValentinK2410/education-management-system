<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для добавления поля moodle_token в таблицу users
 * 
 * Это поле хранит токен Moodle API для каждого пользователя (преподавателя/администратора)
 * для индивидуального доступа к Moodle API
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - добавить поле moodle_token
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('moodle_token')->nullable()->after('moodle_user_id');
        });
    }

    /**
     * Откатить миграцию - удалить поле moodle_token
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('moodle_token');
        });
    }
};
