<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Делает поле program_id nullable для поддержки курсов, синхронизированных из WordPress/Moodle
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Удаляем внешний ключ
            $table->dropForeign(['program_id']);
            
            // Изменяем колонку на nullable
            $table->unsignedBigInteger('program_id')->nullable()->change();
            
            // Восстанавливаем внешний ключ с onDelete('set null') вместо 'cascade'
            $table->foreign('program_id')
                  ->references('id')
                  ->on('programs')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Удаляем внешний ключ
            $table->dropForeign(['program_id']);
            
            // Возвращаем колонку к NOT NULL (но это может вызвать проблемы, если есть NULL значения)
            // Поэтому сначала нужно будет обновить все NULL значения
            $table->unsignedBigInteger('program_id')->nullable(false)->change();
            
            // Восстанавливаем внешний ключ
            $table->foreign('program_id')
                  ->references('id')
                  ->on('programs')
                  ->onDelete('cascade');
        });
    }
};

