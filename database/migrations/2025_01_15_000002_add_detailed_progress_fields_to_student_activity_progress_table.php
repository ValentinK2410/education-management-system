<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для добавления детальных полей прогресса в таблицу прогресса студентов
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию
     */
    public function up(): void
    {
        Schema::table('student_activity_progress', function (Blueprint $table) {
            // Просмотры и чтение
            $table->boolean('is_viewed')->default(false)->after('status'); // Просмотрено ли
            $table->boolean('is_read')->default(false)->after('is_viewed'); // Прочитано ли
            $table->timestamp('last_viewed_at')->nullable()->after('is_read'); // Последний просмотр
            $table->integer('view_count')->default(0)->after('last_viewed_at'); // Количество просмотров
            
            // Черновики и незавершенные работы
            $table->boolean('has_draft')->default(false)->after('view_count'); // Есть ли черновик
            $table->timestamp('draft_created_at')->nullable()->after('has_draft'); // Дата создания черновика
            $table->timestamp('draft_updated_at')->nullable()->after('draft_created_at'); // Дата обновления черновика
            $table->json('draft_data')->nullable()->after('draft_updated_at'); // Данные черновика (JSON)
            
            // Проверка и оценка
            $table->boolean('needs_grading')->default(false)->after('draft_data'); // Требует проверки
            $table->boolean('is_graded')->default(false)->after('needs_grading'); // Проверено ли
            $table->timestamp('grading_requested_at')->nullable()->after('is_graded'); // Дата запроса проверки
            
            // Попытки и статистика
            $table->integer('attempts_count')->default(0)->after('grading_requested_at'); // Количество попыток
            $table->integer('max_attempts')->nullable()->after('attempts_count'); // Максимальное количество попыток
            $table->timestamp('last_attempt_at')->nullable()->after('max_attempts'); // Дата последней попытки
            
            // Вопросы и ответы (для тестов)
            $table->json('questions_data')->nullable()->after('last_attempt_at'); // Данные о вопросах и ответах (JSON)
            $table->integer('correct_answers')->nullable()->after('questions_data'); // Количество правильных ответов
            $table->integer('total_questions')->nullable()->after('correct_answers'); // Всего вопросов
            
            // Дополнительная информация
            $table->json('completion_data')->nullable()->after('total_questions'); // Данные о завершении (JSON)
            $table->decimal('completion_percentage', 5, 2)->nullable()->after('completion_data'); // Процент выполнения
            
            // Индексы для быстрого поиска
            $table->index(['needs_grading', 'is_graded']);
            $table->index(['has_draft', 'status']);
            $table->index('last_viewed_at');
        });
    }

    /**
     * Откатить миграцию
     */
    public function down(): void
    {
        Schema::table('student_activity_progress', function (Blueprint $table) {
            $table->dropIndex(['needs_grading', 'is_graded']);
            $table->dropIndex(['has_draft', 'status']);
            $table->dropIndex('last_viewed_at');
            
            $table->dropColumn([
                'is_viewed', 'is_read', 'last_viewed_at', 'view_count',
                'has_draft', 'draft_created_at', 'draft_updated_at', 'draft_data',
                'needs_grading', 'is_graded', 'grading_requested_at',
                'attempts_count', 'max_attempts', 'last_attempt_at',
                'questions_data', 'correct_answers', 'total_questions',
                'completion_data', 'completion_percentage'
            ]);
        });
    }
};
