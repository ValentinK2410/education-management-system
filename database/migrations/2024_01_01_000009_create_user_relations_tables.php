<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблиц связей пользователей с программами и курсами
 * 
 * Создает таблицы для отслеживания:
 * - На какие программы записан пользователь
 * - На какие курсы записан пользователь
 * - Статус обучения (активное, завершенное, отмененное)
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - создать таблицы связей
     */
    public function up(): void
    {
        // Таблица связей пользователей с программами
        Schema::create('user_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Пользователь
            $table->foreignId('program_id')->constrained()->onDelete('cascade'); // Программа
            $table->enum('status', ['enrolled', 'active', 'completed', 'cancelled'])->default('enrolled'); // Статус обучения
            $table->timestamp('enrolled_at')->useCurrent(); // Дата записи
            $table->timestamp('completed_at')->nullable(); // Дата завершения
            $table->text('notes')->nullable(); // Заметки
            $table->timestamps();
            
            // Уникальная связь пользователь-программа
            $table->unique(['user_id', 'program_id']);
        });

        // Таблица связей пользователей с курсами
        Schema::create('user_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Пользователь
            $table->foreignId('course_id')->constrained()->onDelete('cascade'); // Курс
            $table->enum('status', ['enrolled', 'active', 'completed', 'cancelled'])->default('enrolled'); // Статус обучения
            $table->timestamp('enrolled_at')->useCurrent(); // Дата записи
            $table->timestamp('completed_at')->nullable(); // Дата завершения
            $table->integer('progress')->default(0); // Прогресс обучения (0-100%)
            $table->text('notes')->nullable(); // Заметки
            $table->timestamps();
            
            // Уникальная связь пользователь-курс
            $table->unique(['user_id', 'course_id']);
        });

        // Таблица связей пользователей с учебными заведениями
        Schema::create('user_institutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Пользователь
            $table->foreignId('institution_id')->constrained()->onDelete('cascade'); // Учебное заведение
            $table->enum('status', ['student', 'graduate', 'staff', 'visitor'])->default('student'); // Статус в заведении
            $table->timestamp('enrolled_at')->useCurrent(); // Дата поступления
            $table->timestamp('graduated_at')->nullable(); // Дата выпуска
            $table->text('notes')->nullable(); // Заметки
            $table->timestamps();
            
            // Уникальная связь пользователь-заведение
            $table->unique(['user_id', 'institution_id']);
        });
    }

    /**
     * Откатить миграцию - удалить таблицы связей
     */
    public function down(): void
    {
        Schema::dropIfExists('user_institutions');
        Schema::dropIfExists('user_courses');
        Schema::dropIfExists('user_programs');
    }
};
