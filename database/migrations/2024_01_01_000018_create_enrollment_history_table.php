<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблицы истории зачислений/отчислений
 *
 * Создает таблицу для отслеживания всех изменений статуса обучения:
 * - Зачисление на курс/программу
 * - Активация обучения
 * - Завершение курса/программы
 * - Отчисление
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - создать таблицу истории
     */
    public function up(): void
    {
        Schema::create('enrollment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Пользователь
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('cascade'); // Курс (если применимо)
            $table->foreignId('program_id')->nullable()->constrained()->onDelete('cascade'); // Программа (если применимо)
            $table->enum('entity_type', ['course', 'program']); // Тип сущности
            $table->enum('action', ['enrolled', 'activated', 'completed', 'cancelled', 'reinstated']); // Действие
            $table->enum('old_status', ['enrolled', 'active', 'completed', 'cancelled'])->nullable(); // Старый статус
            $table->enum('new_status', ['enrolled', 'active', 'completed', 'cancelled']); // Новый статус
            $table->text('notes')->nullable(); // Заметки/причина изменения
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null'); // Кто изменил статус
            $table->timestamp('changed_at')->useCurrent(); // Когда изменен статус
            $table->timestamps();

            // Индексы для быстрого поиска
            $table->index(['user_id', 'entity_type']);
            $table->index(['course_id']);
            $table->index(['program_id']);
            $table->index('changed_at');
        });
    }

    /**
     * Откатить миграцию - удалить таблицу истории
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_history');
    }
};
