<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Выполнить миграцию - создать таблицу групп
     */
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // Название группы
            $table->text('description')->nullable();   // Описание группы
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('set null'); // Связь с курсом (опционально)
            $table->foreignId('program_id')->nullable()->constrained('programs')->onDelete('set null'); // Связь с программой (опционально)
            $table->boolean('is_active')->default(true); // Статус активности
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Откатить миграцию - удалить таблицу групп
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
