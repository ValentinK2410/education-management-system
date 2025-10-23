<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Модель роли пользователя
 *
 * Представляет роли в системе управления образованием.
 * Роли определяют права доступа пользователей к различным функциям системы.
 */
class Role extends Model
{
    use HasFactory;

    /**
     * Поля, доступные для массового заполнения
     */
    protected $fillable = [
        'name',        // Название роли
        'slug',        // Уникальный слаг роли
        'description', // Описание роли
    ];

    /**
     * Получить разрешения, связанные с ролью
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /**
     * Получить пользователей, имеющих данную роль
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    /**
     * Проверить, имеет ли роль определенное разрешение
     *
     * @param string $permission Слаг разрешения для проверки
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('slug', $permission)->exists();
    }
}
