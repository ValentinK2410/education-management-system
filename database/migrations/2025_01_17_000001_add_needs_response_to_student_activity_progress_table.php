<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для добавления поля needs_response в таблицу прогресса студентов
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию
     */
    public function up(): void
    {
        Schema::table('student_activity_progress', function (Blueprint $table) {
            // Поле для отслеживания форумов, ожидающих ответа преподавателя
            $table->boolean('needs_response')->default(false)->after('needs_grading');
            
            // Индекс для быстрого поиска форумов, ожидающих ответа
            $table->index('needs_response');
        });
    }

    /**
     * Откатить миграцию
     */
    public function down(): void
    {
        Schema::table('student_activity_progress', function (Blueprint $table) {
            $table->dropIndex(['needs_response']);
            $table->dropColumn('needs_response');
        });
    }
};
