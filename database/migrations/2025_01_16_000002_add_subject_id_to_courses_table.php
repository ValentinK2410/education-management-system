<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для добавления subject_id в таблицу courses
 *
 * Добавляет связь между курсами и предметами.
 * Курс теперь может принадлежать предмету (глобальному курсу).
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('subject_id')->nullable()->after('program_id')->constrained()->onDelete('set null');
            $table->index('subject_id');
        });
    }

    /**
     * Откатить миграцию
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropIndex(['subject_id']);
            $table->dropColumn('subject_id');
        });
    }
};
