<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // Уникальный ключ настройки
            $table->text('value')->nullable(); // Значение настройки
            $table->string('type')->default('string'); // Тип: string, text, integer, boolean, json
            $table->string('group')->default('general'); // Группа настроек: general, moodle, sso, etc.
            $table->string('label')->nullable(); // Название настройки для отображения
            $table->text('description')->nullable(); // Описание настройки
            $table->timestamps();
        });
        
        // Добавляем начальные настройки
        $settings = [
            [
                'key' => 'moodle_url',
                'value' => config('services.moodle.url', ''),
                'type' => 'string',
                'group' => 'moodle',
                'label' => 'URL Moodle',
                'description' => 'Адрес сервера Moodle (например: https://class.dekan.pro)',
            ],
            [
                'key' => 'moodle_token',
                'value' => config('services.moodle.token', ''),
                'type' => 'text',
                'group' => 'moodle',
                'label' => 'Токен Moodle',
                'description' => 'Токен для доступа к REST API Moodle',
            ],
            [
                'key' => 'sso_token',
                'value' => '',
                'type' => 'text',
                'group' => 'sso',
                'label' => 'Токен SSO',
                'description' => 'Токен для единого входа (Single Sign-On)',
            ],
            [
                'key' => 'sso_url',
                'value' => '',
                'type' => 'string',
                'group' => 'sso',
                'label' => 'URL SSO',
                'description' => 'Адрес сервера SSO',
            ],
            [
                'key' => 'site_name',
                'value' => config('app.name', 'EduManage'),
                'type' => 'string',
                'group' => 'general',
                'label' => 'Название сайта',
                'description' => 'Название образовательной платформы',
            ],
            [
                'key' => 'site_email',
                'value' => config('mail.from.address', ''),
                'type' => 'string',
                'group' => 'general',
                'label' => 'Email сайта',
                'description' => 'Email адрес для отправки уведомлений',
            ],
        ];
        
        foreach ($settings as $setting) {
            \DB::table('settings')->insert($setting);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

