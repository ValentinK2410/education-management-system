<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для добавления поля deleted_at в таблицу certificates
 * 
 * Поле необходимо для реализации Soft Deletes (мягкого удаления)
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - добавить поле deleted_at
     */
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Откатить миграцию - удалить поле deleted_at
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};

