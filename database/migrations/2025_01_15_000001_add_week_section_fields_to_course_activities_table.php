<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для добавления полей недели и раздела в таблицу элементов курса
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию
     */
    public function up(): void
    {
        Schema::table('course_activities', function (Blueprint $table) {
            $table->integer('week_number')->nullable()->after('moodle_section_id'); // Номер недели
            $table->integer('section_number')->nullable()->after('week_number'); // Номер раздела
            $table->integer('section_order')->nullable()->after('section_number'); // Порядок в разделе
            $table->string('section_type')->nullable()->after('section_order'); // Тип раздела (week, topic, etc)
            
            // Индексы для быстрого поиска по неделям
            $table->index(['course_id', 'week_number']);
            $table->index(['course_id', 'section_number']);
        });
    }

    /**
     * Откатить миграцию
     */
    public function down(): void
    {
        Schema::table('course_activities', function (Blueprint $table) {
            $table->dropIndex(['course_id', 'week_number']);
            $table->dropIndex(['course_id', 'section_number']);
            $table->dropColumn(['week_number', 'section_number', 'section_order', 'section_type']);
        });
    }
};
