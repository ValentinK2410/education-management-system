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
            [
                'key' => 'system_brand_text_size',
                'value' => '1.5',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Размер текста названия (rem)',
                'description' => 'Размер шрифта названия системы в сайдбаре',
            ],
            [
                'key' => 'system_logo_width',
                'value' => '32',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Ширина логотипа (px)',
                'description' => 'Максимальная ширина логотипа в пикселях',
            ],
            [
                'key' => 'system_logo_height',
                'value' => '32',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Высота логотипа (px)',
                'description' => 'Максимальная высота логотипа в пикселях',
            ],
            [
                'key' => 'system_brand_additional_lines',
                'value' => '[]',
                'type' => 'json',
                'group' => 'general',
                'label' => 'Дополнительные строки',
                'description' => 'Дополнительные строки текста под названием системы (в формате JSON)',
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
