<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблиц ролей и разрешений
 *
 * Создает систему управления доступом на основе ролей (RBAC).
 * Включает таблицы: roles, permissions, role_permissions, user_roles.
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - создать таблицы ролей и разрешений
     */
    public function up(): void
    {
        // Таблица ролей пользователей
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // Название роли
            $table->string('slug')->unique();         // Уникальный слаг роли
            $table->text('description')->nullable();  // Описание роли
            $table->timestamps();
        });

        // Таблица разрешений
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // Название разрешения
            $table->string('slug')->unique();         // Уникальный слаг разрешения
            $table->text('description')->nullable();  // Описание разрешения
            $table->timestamps();
        });

        // Связующая таблица ролей и разрешений (многие ко многим)
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Связующая таблица пользователей и ролей (многие ко многим)
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Откатить миграцию - удалить таблицы ролей и разрешений
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
