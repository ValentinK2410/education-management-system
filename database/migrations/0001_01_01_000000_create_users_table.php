<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблицы пользователей
 *
 * Создает основную таблицу пользователей системы с дополнительными полями
 * для профиля и статуса активности.
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - создать таблицу пользователей
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // Имя пользователя
            $table->string('email')->unique();         // Email адрес (уникальный)
            $table->timestamp('email_verified_at')->nullable(); // Дата подтверждения email
            $table->string('password');                // Пароль (будет захеширован)
            $table->string('phone')->nullable();       // Номер телефона
            $table->string('avatar')->nullable();      // Путь к аватару
            $table->text('bio')->nullable();           // Биография пользователя
            $table->boolean('is_active')->default(true); // Статус активности
            $table->rememberToken();                   // Токен "запомнить меня"
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Откатить миграцию - удалить таблицы пользователей
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
