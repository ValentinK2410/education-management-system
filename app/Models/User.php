<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Модель пользователя системы управления образованием
 *
 * Отвечает за аутентификацию пользователей и управление их ролями.
 * Поддерживает различные роли: администратор, преподаватель, студент.
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Поля, доступные для массового заполнения
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',                    // Имя пользователя
        'email',                   // Email адрес
        'password',                // Пароль (будет захеширован)
        'phone',                   // Номер телефона
        'avatar',                  // Путь к аватару
        'bio',                     // Биография пользователя
        'is_active',               // Статус активности
        'theme_preference',        // Предпочтение темы (light/dark)
        'sidebar_collapsed',       // Свернута ли боковая панель
        'notifications_enabled',   // Включены ли уведомления
    ];

    /**
     * Поля, которые должны быть скрыты при сериализации
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',        // Пароль
        'remember_token',  // Токен "запомнить меня"
    ];

    /**
     * Получить атрибуты, которые должны быть приведены к определенным типам
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',  // Дата подтверждения email
            'password' => 'hashed',            // Пароль будет автоматически хеширован
            'is_active' => 'boolean',          // Статус активности как булево значение
            'sidebar_collapsed' => 'boolean',  // Состояние боковой панели
            'notifications_enabled' => 'boolean', // Включены ли уведомления
        ];
    }

    /**
     * Получить роли пользователя
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Получить курсы, которые преподает пользователь
     *
     * @return HasMany
     */
    public function taughtCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    /**
     * Проверить, имеет ли пользователь определенную роль
     *
     * @param string $role Слаг роли для проверки
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('slug', $role)->exists();
    }

    /**
     * Проверить, является ли пользователь администратором
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Проверить, имеет ли пользователь определенное разрешение
     *
     * @param string $permission Слаг разрешения для проверки
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
            $query->where('slug', $permission);
        })->exists();
    }

    /**
     * Область видимости для получения только активных пользователей
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
