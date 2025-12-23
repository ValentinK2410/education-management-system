<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для добавления индексов в таблицу users для оптимизации поиска
 * 
 * Добавляет индексы для полей name, phone и составной индекс для быстрого поиска
 * при большом количестве пользователей (миллионы записей)
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - добавить индексы для оптимизации поиска
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Индекс для поля name (для поиска по имени)
            $table->index('name', 'users_name_index');
            
            // Индекс для поля phone (для поиска по телефону)
            $table->index('phone', 'users_phone_index');
            
            // Составной индекс для фильтрации по статусу и сортировки
            $table->index(['is_active', 'name'], 'users_active_name_index');
        });
    }

    /**
     * Откатить миграцию - удалить индексы
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_name_index');
            $table->dropIndex('users_phone_index');
            $table->dropIndex('users_active_name_index');
        });
    }
};
