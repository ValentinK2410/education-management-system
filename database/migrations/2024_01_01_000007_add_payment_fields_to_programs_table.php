<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для добавления дополнительных полей в таблицу программ
 * 
 * Добавляет поля для управления статусом программы (активная/неактивная)
 * и типом оплаты (платная/бесплатная).
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - добавить поля статуса и оплаты программ
     */
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false)->after('is_active'); // Платная программа или бесплатная
            $table->decimal('price', 10, 2)->nullable()->after('is_paid'); // Цена программы
            $table->string('currency', 3)->default('RUB')->after('price'); // Валюта
        });
    }

    /**
     * Откатить миграцию - удалить поля статуса и оплаты программ
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['is_paid', 'price', 'currency']);
        });
    }
};
