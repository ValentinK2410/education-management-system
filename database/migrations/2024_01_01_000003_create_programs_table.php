<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблицы образовательных программ
 *
 * Создает таблицу для хранения информации об образовательных программах.
 * Связывает программы с учебными заведениями и включает детали обучения.
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - создать таблицу образовательных программ
     */
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');                           // Название образовательной программы
            $table->text('description')->nullable();          // Описание программы
            $table->foreignId('institution_id')->constrained()->onDelete('cascade'); // Связь с учебным заведением
            $table->string('duration')->nullable();           // Продолжительность программы (например, "4 года", "2 семестра")
            $table->string('degree_level')->nullable();       // Уровень степени (бакалавр, магистр, доктор)
            $table->decimal('tuition_fee', 10, 2)->nullable(); // Стоимость обучения
            $table->string('language')->default('ru');         // Язык обучения
            $table->json('requirements')->nullable();         // Требования для поступления (JSON массив)
            $table->boolean('is_active')->default(true);      // Статус активности
            $table->timestamps();
        });
    }

    /**
     * Откатить миграцию - удалить таблицу образовательных программ
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
