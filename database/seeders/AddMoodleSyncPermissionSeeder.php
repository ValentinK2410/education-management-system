<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Сидер для добавления права синхронизации с Moodle
 */
class AddMoodleSyncPermissionSeeder extends Seeder
{
    /**
     * Запустить сидер
     */
    public function run(): void
    {
        // Создаем право на синхронизацию с Moodle
        $permission = Permission::firstOrCreate(
            ['slug' => 'sync_moodle'],
            [
                'name' => 'Синхронизация с Moodle',
                'slug' => 'sync_moodle',
                'description' => 'Право на синхронизацию курсов и записей студентов из Moodle'
            ]
        );

        // Если право уже существовало, обновляем его описание
        if (!$permission->wasRecentlyCreated) {
            $permission->update([
                'name' => 'Синхронизация с Moodle',
                'description' => 'Право на синхронизацию курсов и записей студентов из Moodle'
            ]);
        }

        // Назначаем право роли администратора по умолчанию
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching([$permission->id]);
            $this->command->info('Право синхронизации с Moodle добавлено роли администратора');
        }

        $this->command->info('Право синхронизации с Moodle успешно добавлено!');
    }
}

