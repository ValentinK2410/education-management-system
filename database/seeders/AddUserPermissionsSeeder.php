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
            [
                'name' => 'Скрывать ссылки на пользователей',
                'slug' => 'hide_user_links',
                'description' => 'Право на скрытие ссылок на пользователей в интерфейсе'
            ],
            [
                'name' => 'Видеть раздел "Пользователи" в боковой панели',
                'slug' => 'view_sidebar_users',
                'description' => 'Право на отображение ссылки "Пользователи" в боковой панели'
            ],
            [
                'name' => 'Видеть раздел "Учебные заведения" в боковой панели',
                'slug' => 'view_sidebar_institutions',
                'description' => 'Право на отображение ссылки "Учебные заведения" в боковой панели'
            ],
            [
                'name' => 'Видеть раздел "Программы" в боковой панели',
                'slug' => 'view_sidebar_programs',
                'description' => 'Право на отображение ссылки "Программы" в боковой панели'
            ],
            [
                'name' => 'Видеть раздел "Курсы" в боковой панели',
                'slug' => 'view_sidebar_courses',
                'description' => 'Право на отображение ссылки "Курсы" в боковой панели'
            ],
            [
                'name' => 'Видеть раздел "Роли" в боковой панели',
                'slug' => 'view_sidebar_roles',
                'description' => 'Право на отображение ссылки "Роли" в боковой панели'
            ],
            [
                'name' => 'Видеть раздел "Отзывы" в боковой панели',
                'slug' => 'view_sidebar_reviews',
                'description' => 'Право на отображение ссылки "Отзывы" в боковой панели'
            ],
            [
                'name' => 'Видеть раздел "Шаблоны сертификатов" в боковой панели',
                'slug' => 'view_sidebar_certificates',
                'description' => 'Право на отображение ссылки "Шаблоны сертификатов" в боковой панели'
            ],
            [
                'name' => 'Видеть раздел "Архив пользователей" в боковой панели',
                'slug' => 'view_sidebar_archive',
                'description' => 'Право на отображение ссылки "Архив пользователей" в боковой панели'
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
            $newPermissionSlugs = [
                'view_users', 'edit_users', 'delete_users', 'hide_user_links',
                'view_sidebar_users', 'view_sidebar_institutions', 'view_sidebar_programs',
                'view_sidebar_courses', 'view_sidebar_roles', 'view_sidebar_reviews',
                'view_sidebar_certificates', 'view_sidebar_archive'
            ];
            
            $newPermissionIds = Permission::whereIn('slug', $newPermissionSlugs)
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
