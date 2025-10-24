<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Название события
            $table->text('description')->nullable(); // Описание события
            $table->text('content')->nullable(); // Подробное содержание
            $table->datetime('start_date'); // Дата и время начала
            $table->datetime('end_date')->nullable(); // Дата и время окончания
            $table->string('location')->nullable(); // Место проведения
            $table->string('image')->nullable(); // Изображение события
            $table->boolean('is_published')->default(false); // Опубликовано ли событие
            $table->boolean('is_featured')->default(false); // Рекомендуемое событие
            $table->integer('max_participants')->nullable(); // Максимальное количество участников
            $table->decimal('price', 10, 2)->nullable(); // Цена участия
            $table->string('currency', 3)->default('RUB'); // Валюта
            $table->string('registration_url')->nullable(); // Ссылка на регистрацию
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index(['is_published', 'start_date']);
            $table->index(['is_featured', 'is_published']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
