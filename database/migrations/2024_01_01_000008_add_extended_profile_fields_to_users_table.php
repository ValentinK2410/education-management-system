<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для расширения профиля пользователей
 * 
 * Добавляет поля для детального профиля пользователя:
 * фото, вероисповедание, город, церковь, семейное положение,
 * образование, краткая биография.
 */
return new class extends Migration
{
    /**
     * Выполнить миграцию - добавить поля расширенного профиля
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('avatar'); // Фото пользователя
            $table->string('religion')->nullable()->after('photo'); // Вероисповедание
            $table->string('city')->nullable()->after('religion'); // Город
            $table->string('church')->nullable()->after('city'); // Название церкви
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable()->after('church'); // Семейное положение
            $table->string('education')->nullable()->after('marital_status'); // Образование
            $table->text('about_me')->nullable()->after('education'); // Кратко о себе
        });
    }

    /**
     * Откатить миграцию - удалить поля расширенного профиля
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'photo', 'religion', 'city', 'church', 
                'marital_status', 'education', 'about_me'
            ]);
        });
    }
};
