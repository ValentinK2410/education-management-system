<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблицы курсов
 *
 * Создает таблицу для хранения информации об отдельных курсах.
 * Связывает курсы с образовательными программами и преподавателями.
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - создать таблицу курсов
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');                           // Название курса
            $table->text('description')->nullable();          // Описание курса
            $table->foreignId('program_id')->constrained()->onDelete('cascade'); // Связь с образовательной программой
            $table->foreignId('instructor_id')->nullable()->constrained('users')->onDelete('set null'); // Связь с преподавателем
            $table->string('code')->nullable();              // Код курса
            $table->integer('credits')->nullable();           // Количество кредитов
            $table->string('duration')->nullable();           // Продолжительность курса
            $table->string('schedule')->nullable();           // Расписание занятий
            $table->string('location')->nullable();           // Место проведения занятий
            $table->json('prerequisites')->nullable();        // Предварительные требования (JSON массив)
            $table->json('learning_outcomes')->nullable();   // Результаты обучения (JSON массив)
            $table->boolean('is_active')->default(true);      // Статус активности
            $table->timestamps();
        });
    }

    /**
     * Откатить миграцию - удалить таблицу курсов
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
