<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблицы истории действий студентов
 * 
 * Создает таблицу для хранения истории всех действий студентов:
 * - Когда сдано задание
 * - Когда проверено
 * - Кто проверил
 * - Комментарии и изменения
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - создать таблицу истории действий
     */
    public function up(): void
    {
        Schema::create('student_activity_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Студент
            $table->foreignId('course_id')->constrained()->onDelete('cascade'); // Курс
            $table->foreignId('activity_id')->constrained('course_activities')->onDelete('cascade'); // Элемент курса
            $table->enum('action_type', ['started', 'submitted', 'graded', 'viewed', 'commented', 'updated', 'completed']); // Тип действия
            $table->json('action_data')->nullable(); // Данные действия (JSON)
            $table->foreignId('performed_by_user_id')->nullable()->constrained('users')->onDelete('set null'); // Кто выполнил действие
            $table->text('description')->nullable(); // Описание действия
            $table->timestamp('created_at'); // Когда выполнено действие
            
            // Индексы для быстрого поиска
            $table->index(['user_id', 'course_id']);
            $table->index(['activity_id', 'action_type']);
            $table->index('created_at');
            $table->index('performed_by_user_id');
        });
    }

    /**
     * Откатить миграцию - удалить таблицу истории действий
     */
    public function down(): void
    {
        Schema::dropIfExists('student_activity_history');
    }
};

