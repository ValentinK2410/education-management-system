<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблицы версий данных
 * 
 * Таблица хранит историю изменений записей для возможности отката
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - создать таблицу версий
     */
    public function up(): void
    {
        Schema::create('data_versions', function (Blueprint $table) {
            $table->id();
            $table->string('versionable_type'); // Тип модели (User, Course, etc.)
            $table->unsignedBigInteger('versionable_id'); // ID записи
            $table->json('data'); // Данные записи на момент версии
            $table->unsignedBigInteger('created_by')->nullable(); // Кто создал версию
            $table->string('action')->default('updated'); // Действие: created, updated, deleted
            $table->text('notes')->nullable(); // Заметки о версии
            $table->timestamps();

            // Индексы для быстрого поиска
            $table->index(['versionable_type', 'versionable_id']);
            $table->index('created_at');
            $table->index('created_by');
            
            // Внешний ключ для created_by
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Откатить миграцию - удалить таблицу версий
     */
    public function down(): void
    {
        Schema::dropIfExists('data_versions');
    }
};

