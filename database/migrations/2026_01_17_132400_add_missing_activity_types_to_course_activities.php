<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Миграция для добавления недостающих типов активностей в ENUM
 *
 * Добавляет типы: page, file, folder, url, book и другие возможные типы из Moodle
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - изменить ENUM на VARCHAR для поддержки всех типов из Moodle
     */
    public function up(): void
    {
        // Изменяем тип колонки с ENUM на VARCHAR для поддержки всех типов из Moodle
        DB::statement("ALTER TABLE `course_activities` MODIFY `activity_type` VARCHAR(50) NOT NULL");

        // Добавляем индекс для быстрого поиска по типу активности
        Schema::table('course_activities', function (Blueprint $table) {
            if (!$this->hasIndex('course_activities', 'activity_type')) {
                $table->index('activity_type');
            }
        });
    }

    /**
     * Откатить миграцию - вернуть ENUM (но с расширенным списком)
     */
    public function down(): void
    {
        // Удаляем индекс, если он был создан
        Schema::table('course_activities', function (Blueprint $table) {
            if ($this->hasIndex('course_activities', 'activity_type')) {
                $table->dropIndex(['activity_type']);
            }
        });

        // Возвращаем ENUM с расширенным списком типов
        DB::statement("ALTER TABLE `course_activities` MODIFY `activity_type` ENUM('assign', 'quiz', 'forum', 'resource', 'page', 'file', 'folder', 'url', 'book', 'other') NOT NULL");
    }

    /**
     * Проверить наличие индекса
     */
    private function hasIndex(string $table, string $index): bool
    {
        $indexes = DB::select("SHOW INDEXES FROM `{$table}` WHERE Key_name = ?", [$index]);
        return !empty($indexes);
    }
};
