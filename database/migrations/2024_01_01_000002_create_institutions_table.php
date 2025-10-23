<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблицы учебных заведений
 *
 * Создает таблицу для хранения информации об образовательных учреждениях.
 * Включает основные данные: название, описание, контакты, логотип.
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - создать таблицу учебных заведений
     */
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // Название учебного заведения
            $table->text('description')->nullable();  // Описание учебного заведения
            $table->string('address')->nullable();    // Адрес учебного заведения
            $table->string('phone')->nullable();       // Телефон учебного заведения
            $table->string('email')->nullable();       // Email учебного заведения
            $table->string('website')->nullable();    // Веб-сайт учебного заведения
            $table->string('logo')->nullable();        // Путь к логотипу учебного заведения
            $table->boolean('is_active')->default(true); // Статус активности
            $table->timestamps();
        });
    }

    /**
     * Откатить миграцию - удалить таблицу учебных заведений
     */
    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
