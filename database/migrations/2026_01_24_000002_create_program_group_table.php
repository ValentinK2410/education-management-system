<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Выполнить миграцию - создать таблицу связей программ и групп
     */
    public function up(): void
    {
        Schema::create('program_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->timestamp('attached_at')->useCurrent(); // Дата прикрепления группы к программе
            $table->text('notes')->nullable(); // Заметки
            $table->timestamps();

            // Уникальная связь программа-группа
            $table->unique(['program_id', 'group_id'], 'program_group_unique');
        });
    }

    /**
     * Откатить миграцию - удалить таблицу связей
     */
    public function down(): void
    {
        Schema::dropIfExists('program_group');
    }
};
