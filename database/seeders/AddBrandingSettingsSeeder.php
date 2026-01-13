<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class AddBrandingSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'system_name',
                'value' => 'EduManage',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Название системы',
                'description' => 'Название системы, отображаемое в сайдбаре',
            ],
            [
                'key' => 'system_logo',
                'value' => null,
                'type' => 'string',
                'group' => 'general',
                'label' => 'Логотип системы',
                'description' => 'Путь к файлу логотипа (изображение будет отображаться вместо иконки)',
            ],
            [
                'key' => 'system_logo_icon',
                'value' => 'fa-graduation-cap',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Иконка системы',
                'description' => 'Класс Font Awesome иконки (используется, если логотип не загружен)',
            ],
        ];

        foreach ($settings as $settingData) {
            Setting::updateOrCreate(
                ['key' => $settingData['key']],
                $settingData
            );
        }
    }
}
