<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблицы предметов
 *
 * Создает таблицу для хранения информации о предметах (глобальных курсах).
 * Предмет объединяет несколько курсов одной тематики.
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - создать таблицу предметов
     */
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');                           // Название предмета
            $table->text('description')->nullable();          // Описание предмета
            $table->string('code')->nullable();               // Код предмета
            $table->text('short_description')->nullable();   // Краткое описание
            $table->string('image')->nullable();              // Изображение предмета
            $table->integer('order')->default(0);             // Порядок отображения
            $table->boolean('is_active')->default(true);      // Статус активности
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('is_active');
            $table->index('order');
        });
    }

    /**
     * Откатить миграцию - удалить таблицу предметов
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
