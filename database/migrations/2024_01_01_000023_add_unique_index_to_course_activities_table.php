<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Миграция для добавления уникального индекса к существующей таблице course_activities
 * 
 * Исправляет проблему с длинным именем индекса в MySQL
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - добавить уникальный индекс
     */
    public function up(): void
    {
        // Проверяем, существует ли таблица
        if (Schema::hasTable('course_activities')) {
            // Проверяем, существует ли уже индекс
            $indexes = DB::select("SHOW INDEXES FROM course_activities WHERE Key_name = 'course_activities_unique'");
            
            if (empty($indexes)) {
                // Добавляем уникальный индекс с коротким именем
                Schema::table('course_activities', function (Blueprint $table) {
                    $table->unique(['course_id', 'moodle_activity_id', 'activity_type'], 'course_activities_unique');
                });
            }
        }
    }

    /**
     * Откатить миграцию - удалить уникальный индекс
     */
    public function down(): void
    {
        if (Schema::hasTable('course_activities')) {
            Schema::table('course_activities', function (Blueprint $table) {
                $table->dropUnique('course_activities_unique');
            });
        }
    }
};

