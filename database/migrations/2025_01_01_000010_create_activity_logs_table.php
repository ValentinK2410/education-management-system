<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблицы логов активности
 * 
 * Таблица хранит логи всех критических операций в системе
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - создать таблицу логов
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('loggable_type')->nullable(); // Тип модели (User, Course, etc.)
            $table->unsignedBigInteger('loggable_id')->nullable(); // ID записи
            $table->string('action'); // Действие: created, updated, deleted, restored
            $table->unsignedBigInteger('user_id')->nullable(); // Кто выполнил действие
            $table->string('ip_address', 45)->nullable(); // IP адрес
            $table->string('user_agent')->nullable(); // User Agent
            $table->json('old_values')->nullable(); // Старые значения
            $table->json('new_values')->nullable(); // Новые значения
            $table->text('description')->nullable(); // Описание действия
            $table->json('properties')->nullable(); // Дополнительные свойства
            $table->timestamps();

            // Индексы для быстрого поиска
            $table->index(['loggable_type', 'loggable_id']);
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
            
            // Внешний ключ для user_id
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Откатить миграцию - удалить таблицу логов
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};

