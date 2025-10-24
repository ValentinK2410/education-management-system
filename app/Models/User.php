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
        'photo',                   // Фото пользователя
        'religion',                // Вероисповедание
        'city',                    // Город
        'church',                  // Название церкви
        'marital_status',          // Семейное положение
        'education',               // Образование
        'about_me',                // Кратко о себе
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

    /**
     * Получить программы, на которые записан пользователь
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function programs()
    {
        return $this->belongsToMany(Program::class, 'user_programs')
                    ->withPivot(['status', 'enrolled_at', 'completed_at', 'notes'])
                    ->withTimestamps();
    }

    /**
     * Получить курсы, на которые записан пользователь
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'user_courses')
                    ->withPivot(['status', 'enrolled_at', 'completed_at', 'progress', 'notes'])
                    ->withTimestamps();
    }

    /**
     * Получить учебные заведения, с которыми связан пользователь
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function institutions()
    {
        return $this->belongsToMany(Institution::class, 'user_institutions')
                    ->withPivot(['status', 'enrolled_at', 'graduated_at', 'notes'])
                    ->withTimestamps();
    }

    /**
     * Получить активные программы пользователя
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function activePrograms()
    {
        return $this->programs()->wherePivot('status', 'active');
    }

    /**
     * Получить активные курсы пользователя
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function activeCourses()
    {
        return $this->courses()->wherePivot('status', 'active');
    }

    /**
     * Получить завершенные программы пользователя
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function completedPrograms()
    {
        return $this->programs()->wherePivot('status', 'completed');
    }

    /**
     * Получить завершенные курсы пользователя
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function completedCourses()
    {
        return $this->courses()->wherePivot('status', 'completed');
    }

    /**
     * Отзывы пользователя
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
