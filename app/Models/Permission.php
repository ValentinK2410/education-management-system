<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Модель разрешения
 *
 * Представляет разрешения в системе управления образованием.
 * Разрешения определяют конкретные действия, которые может выполнять пользователь.
 */
class Permission extends Model
{
    use HasFactory;

    /**
     * Поля, доступные для массового заполнения
     */
    protected $fillable = [
        'name',        // Название разрешения
        'slug',        // Уникальный слаг разрешения
        'description', // Описание разрешения
    ];

    /**
     * Получить роли, которые имеют данное разрешение
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }
}
