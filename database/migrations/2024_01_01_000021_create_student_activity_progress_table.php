<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблицы прогресса студентов по элементам курса
 * 
 * Создает таблицу для отслеживания прогресса студентов:
 * - Статус выполнения элемента
 * - Оценки
 * - Даты сдачи и проверки
 * - Кто проверил
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - создать таблицу прогресса студентов
     */
    public function up(): void
    {
        Schema::create('student_activity_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Студент
            $table->foreignId('course_id')->constrained()->onDelete('cascade'); // Курс
            $table->foreignId('activity_id')->constrained('course_activities')->onDelete('cascade'); // Элемент курса
            $table->enum('status', ['not_started', 'in_progress', 'submitted', 'graded', 'completed'])->default('not_started'); // Статус
            $table->decimal('grade', 10, 2)->nullable(); // Оценка студента
            $table->decimal('max_grade', 10, 2)->nullable(); // Максимальная оценка
            $table->timestamp('started_at')->nullable(); // Дата начала выполнения
            $table->timestamp('submitted_at')->nullable(); // Дата сдачи
            $table->timestamp('graded_at')->nullable(); // Дата проверки
            $table->foreignId('graded_by_user_id')->nullable()->constrained('users')->onDelete('set null'); // Кто проверил
            $table->text('feedback')->nullable(); // Комментарий преподавателя
            $table->json('progress_data')->nullable(); // Дополнительные данные прогресса (JSON)
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index(['user_id', 'course_id']);
            $table->index(['activity_id', 'status']);
            $table->index('graded_at');
            $table->unique(['user_id', 'course_id', 'activity_id']);
        });
    }

    /**
     * Откатить миграцию - удалить таблицу прогресса студентов
     */
    public function down(): void
    {
        Schema::dropIfExists('student_activity_progress');
    }
};

