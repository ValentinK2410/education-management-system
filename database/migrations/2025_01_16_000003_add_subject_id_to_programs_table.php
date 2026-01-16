<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для добавления subject_id в таблицу programs
 *
 * Добавляет связь между программами и предметами.
 * Программа теперь содержит предметы вместо курсов.
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию
     */
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            // Создаем промежуточную таблицу для связи many-to-many между programs и subjects
            // Это позволит программе содержать несколько предметов
        });
        
        // Создаем промежуточную таблицу program_subject
        Schema::create('program_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0); // Порядок предмета в программе
            $table->timestamps();
            
            $table->unique(['program_id', 'subject_id']);
            $table->index('order');
        });
    }

    /**
     * Откатить миграцию
     */
    public function down(): void
    {
        Schema::dropIfExists('program_subject');
    }
};
