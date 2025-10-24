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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Пользователь, оставивший отзыв
            $table->foreignId('course_id')->constrained()->onDelete('cascade'); // Курс, к которому относится отзыв
            $table->integer('rating')->unsigned()->min(1)->max(5); // Рейтинг от 1 до 5
            $table->text('comment'); // Текст отзыва
            $table->boolean('is_approved')->default(false); // Одобрен ли отзыв администратором
            $table->timestamps();
            
            // Индекс для быстрого поиска отзывов по курсу
            $table->index(['course_id', 'is_approved']);
            // Уникальный индекс - один пользователь может оставить только один отзыв на курс
            $table->unique(['user_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
