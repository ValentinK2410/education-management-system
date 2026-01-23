<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Выполнить миграцию - создать pivot таблицу для связи пользователей и групп
     */
    public function up(): void
    {
        Schema::create('user_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Пользователь (студент)
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade'); // Группа
            $table->timestamp('enrolled_at')->useCurrent(); // Дата зачисления в группу
            $table->text('notes')->nullable(); // Заметки
            $table->timestamps();

            // Уникальная связь пользователь-группа
            $table->unique(['user_id', 'group_id'], 'user_groups_unique');
        });
    }

    /**
     * Откатить миграцию - удалить pivot таблицу
     */
    public function down(): void
    {
        Schema::dropIfExists('user_groups');
    }
};
