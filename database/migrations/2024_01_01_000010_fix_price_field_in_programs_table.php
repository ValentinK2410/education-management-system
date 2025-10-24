<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для исправления поля price в таблице programs
 * 
 * Исправляет тип данных поля price для корректной работы с Laravel.
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - исправить поле price
     */
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            // Удаляем старое поле price
            $table->dropColumn('price');
        });

        Schema::table('programs', function (Blueprint $table) {
            // Добавляем новое поле price с правильным типом
            $table->decimal('price', 10, 2)->nullable()->after('is_paid');
        });
    }

    /**
     * Откатить миграцию - вернуть старое поле price
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('price');
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('is_paid');
        });
    }
};
