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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('program_id')->constrained()->onDelete('cascade');
            $table->foreignId('instructor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('code')->nullable(); // код курса
            $table->integer('credits')->nullable(); // количество кредитов
            $table->string('duration')->nullable(); // продолжительность курса
            $table->string('schedule')->nullable(); // расписание
            $table->string('location')->nullable(); // место проведения
            $table->json('prerequisites')->nullable(); // предварительные требования
            $table->json('learning_outcomes')->nullable(); // результаты обучения
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
