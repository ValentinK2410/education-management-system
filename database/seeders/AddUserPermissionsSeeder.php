<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Сидер для добавления прав работы с пользователями
 * 
 * Добавляет следующие права:
 * - view_users - просматривать пользователей
 * - edit_users - редактировать пользователей
 * - delete_users - удалять пользователей
 */
class AddUserPermissionsSeeder extends Seeder
{
    /**
     * Запустить сидер
     */
    public function run(): void
    {
        // Создаем новые права
        $permissions = [
            [
                'name' => 'Просматривать пользователей',
                'slug' => 'view_users',
                'description' => 'Право на просмотр списка пользователей и их профилей'
            ],
            [
                'name' => 'Редактировать пользователей',
                'slug' => 'edit_users',
                'description' => 'Право на редактирование информации о пользователях'
            ],
            [
                'name' => 'Удалять пользователей',
                'slug' => 'delete_users',
                'description' => 'Право на удаление пользователей из системы'
            ],
        ];

        foreach ($permissions as $permissionData) {
            // Проверяем, существует ли право, если нет - создаем
            $permission = Permission::firstOrCreate(
                ['slug' => $permissionData['slug']],
                $permissionData
            );

            // Если право уже существовало, обновляем его описание
            if ($permission->wasRecentlyCreated === false) {
                $permission->update([
                    'name' => $permissionData['name'],
                    'description' => $permissionData['description']
                ]);
            }
        }

        // Назначаем все новые права роли администратора
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $newPermissionIds = Permission::whereIn('slug', ['view_users', 'edit_users', 'delete_users'])
                ->pluck('id');
            
            // Добавляем новые права к существующим правам администратора
            $existingPermissionIds = $adminRole->permissions()->pluck('permissions.id');
            $allPermissionIds = $existingPermissionIds->merge($newPermissionIds)->unique();
            $adminRole->permissions()->sync($allPermissionIds);
            
            $this->command->info('Новые права добавлены роли администратора');
        }

        $this->command->info('Права работы с пользователями успешно добавлены!');
    }
}
