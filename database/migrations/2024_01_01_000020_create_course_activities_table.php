<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблицы элементов курса
 * 
 * Создает таблицу для хранения всех элементов курса из Moodle:
 * - Задания (assignments)
 * - Тесты/квизы (quizzes)
 * - Форумы (forums)
 * - Материалы (resources)
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - создать таблицу элементов курса
     */
    public function up(): void
    {
        Schema::create('course_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade'); // Курс
            $table->integer('moodle_activity_id')->nullable(); // ID элемента в Moodle
            $table->enum('activity_type', ['assign', 'quiz', 'forum', 'resource', 'other']); // Тип элемента
            $table->string('name'); // Название элемента
            $table->string('section_name')->nullable(); // Название раздела курса
            $table->integer('moodle_section_id')->nullable(); // ID раздела в Moodle
            $table->decimal('max_grade', 10, 2)->nullable(); // Максимальная оценка
            $table->timestamp('due_date')->nullable(); // Срок сдачи
            $table->text('description')->nullable(); // Описание элемента
            $table->json('meta')->nullable(); // Дополнительные метаданные (JSON)
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index(['course_id', 'activity_type']);
            $table->index('moodle_activity_id');
            $table->unique(['course_id', 'moodle_activity_id', 'activity_type'], 'course_activities_unique');
        });
    }

    /**
     * Откатить миграцию - удалить таблицу элементов курса
     */
    public function down(): void
    {
        Schema::dropIfExists('course_activities');
    }
};

