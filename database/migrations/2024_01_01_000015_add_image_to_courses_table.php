<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для добавления поля изображения обложки в таблицу курсов
 *
 * Добавляет поле для хранения пути к изображению обложки курса.
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - добавить поле изображения обложки
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('image')->nullable()->after('description'); // Путь к изображению обложки курса
        });
    }

    /**
     * Откатить миграцию - удалить поле изображения обложки
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};

