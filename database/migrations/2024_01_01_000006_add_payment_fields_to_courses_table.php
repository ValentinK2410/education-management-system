<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для добавления дополнительных полей в таблицу курсов
 * 
 * Добавляет поля для управления статусом курса (активный/неактивный)
 * и типом оплаты (платный/бесплатный).
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - добавить поля статуса и оплаты курсов
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false)->after('is_active'); // Платный курс или бесплатный
            $table->decimal('price', 10, 2)->nullable()->after('is_paid'); // Цена курса
            $table->string('currency', 3)->default('RUB')->after('price'); // Валюта
        });
    }

    /**
     * Откатить миграцию - удалить поля статуса и оплаты курсов
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['is_paid', 'price', 'currency']);
        });
    }
};
