<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для добавления поля moodle_user_id в таблицу users
 * 
 * Это поле хранит ID пользователя в Moodle для синхронизации между системами
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - добавить поле moodle_user_id
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('moodle_user_id')->nullable()->after('id');
            $table->index('moodle_user_id');
        });
    }

    /**
     * Откатить миграцию - удалить поле moodle_user_id
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['moodle_user_id']);
            $table->dropColumn('moodle_user_id');
        });
    }
};

