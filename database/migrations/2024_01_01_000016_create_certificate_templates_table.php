<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблицы шаблонов сертификатов
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию
     */
    public function up(): void
    {
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название шаблона
            $table->enum('type', ['course', 'program']); // Тип сертификата (для курса или программы)
            $table->text('description')->nullable(); // Описание шаблона
            
            // Параметры изображения
            $table->integer('width')->default(1200); // Ширина в пикселях
            $table->integer('height')->default(800); // Высота в пикселях
            $table->integer('quality')->default(90); // Качество изображения (1-100)
            
            // Фон шаблона
            $table->string('background_type')->default('color'); // color, image, gradient
            $table->string('background_color')->default('#ffffff'); // Цвет фона
            $table->string('background_image')->nullable(); // Путь к изображению фона
            $table->text('background_gradient')->nullable(); // JSON градиента
            
            // Настройки текста
            $table->json('text_elements')->nullable(); // JSON с элементами текста (название, имя, дата и т.д.)
            
            // Статус
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false); // Шаблон по умолчанию
            
            $table->timestamps();
        });
    }

    /**
     * Откатить миграцию
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_templates');
    }
};
