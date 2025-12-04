<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблицы выданных сертификатов
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию
     */
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Пользователь
            $table->foreignId('certificate_template_id')->constrained()->onDelete('cascade'); // Шаблон
            $table->string('certificate_number')->unique(); // Уникальный номер сертификата

            // Связь с курсом или программой
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('program_id')->nullable()->constrained()->onDelete('cascade');

            // Путь к сгенерированному изображению
            $table->string('image_path'); // Путь к сохраненному изображению сертификата

            // Данные для отображения на сертификате
            $table->json('data')->nullable(); // JSON с данными (имя, дата завершения и т.д.)

            // Дата выдачи
            $table->timestamp('issued_at')->useCurrent();

            $table->timestamps();

            // Индексы
            $table->index('user_id');
            $table->index('certificate_number');
        });
    }

    /**
     * Откатить миграцию
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
