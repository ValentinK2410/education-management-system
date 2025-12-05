<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для добавления статуса оплаты в таблицы связей
 * 
 * Добавляет поле payment_status в user_courses и user_programs
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - добавить поля статуса оплаты
     */
    public function up(): void
    {
        Schema::table('user_courses', function (Blueprint $table) {
            $table->enum('payment_status', ['not_required', 'pending', 'paid', 'failed'])->default('not_required')->after('progress');
        });

        Schema::table('user_programs', function (Blueprint $table) {
            $table->enum('payment_status', ['not_required', 'pending', 'paid', 'failed'])->default('not_required')->after('status');
        });
    }

    /**
     * Откатить миграцию - удалить поля статуса оплаты
     */
    public function down(): void
    {
        Schema::table('user_courses', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });

        Schema::table('user_programs', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }
};
