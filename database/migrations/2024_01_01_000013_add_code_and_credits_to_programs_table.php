<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для добавления полей code и credits в таблицу programs
 *
 * Добавляет код программы и количество кредитов
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - добавить поля в таблицу programs
     */
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->string('code', 50)->nullable()->after('name');           // Код программы
            $table->integer('credits')->nullable()->after('duration');       // Количество кредитов
        });
    }

    /**
     * Откатить миграцию - удалить поля из таблицы programs
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['code', 'credits']);
        });
    }
};

