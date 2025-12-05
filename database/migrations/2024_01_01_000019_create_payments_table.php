<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблицы платежей
 * 
 * Создает таблицу для отслеживания платежей за курсы и программы:
 * - Оплата курса
 * - Оплата программы
 * - Статус оплаты (pending, paid, failed, refunded)
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - создать таблицу платежей
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Пользователь
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('cascade'); // Курс (если применимо)
            $table->foreignId('program_id')->nullable()->constrained()->onDelete('cascade'); // Программа (если применимо)
            $table->enum('entity_type', ['course', 'program']); // Тип сущности
            $table->decimal('amount', 10, 2); // Сумма платежа
            $table->string('currency', 3)->default('RUB'); // Валюта
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded', 'cancelled'])->default('pending'); // Статус платежа
            $table->string('payment_method')->nullable(); // Способ оплаты
            $table->string('transaction_id')->nullable()->unique(); // ID транзакции
            $table->text('notes')->nullable(); // Заметки
            $table->timestamp('paid_at')->nullable(); // Дата оплаты
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index(['user_id', 'entity_type']);
            $table->index(['course_id']);
            $table->index(['program_id']);
            $table->index('status');
            $table->index('paid_at');
        });
    }

    /**
     * Откатить миграцию - удалить таблицу платежей
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
