<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\ChecksDependencies;
use App\Traits\LogsActivity;
use App\Traits\Versionable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    use HasFactory, Notifiable, SoftDeletes, Versionable, ChecksDependencies, LogsActivity;

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
        'moodle_user_id',          // ID пользователя в Moodle
        'moodle_token',            // Токен Moodle API для пользователя
        'users_per_page',          // Количество пользователей на странице
    ];

    /**
     * Поля, которые должны быть скрыты при сериализации
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',        // Пароль
        'remember_token',  // Токен "запомнить меня"
        'moodle_token',    // Токен Moodle API (скрыт для безопасности)
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
            'users_per_page' => 'integer',     // Количество пользователей на странице
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
     * Курсы, где пользователь указан как преподаватель (множественная связь)
     *
     * @return BelongsToMany
     */
    public function instructedCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_instructors')
            ->withPivot(['source', 'moodle_role_shortname', 'is_primary'])
            ->withTimestamps();
    }

    /**
     * Проверить, имеет ли пользователь определенную роль
     *
     * @param string $role Слаг роли для проверки
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        // Если переключены на роль, проверяем только переключенную роль
        if (session('role_switched') && session('switched_role_slug')) {
            return session('switched_role_slug') === $role;
        }

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
        // Если переключены на роль, проверяем разрешения только переключенной роли
        if (session('role_switched') && session('switched_role_id')) {
            $switchedRole = \App\Models\Role::find(session('switched_role_id'));
            if ($switchedRole) {
                return $switchedRole->hasPermission($permission);
            }
        }

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
     * Получить прогресс по элементам курса
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activityProgress()
    {
        return $this->hasMany(StudentActivityProgress::class);
    }

    /**
     * Получить историю действий по элементам курса
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activityHistory()
    {
        return $this->hasMany(StudentActivityHistory::class);
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
     * Включает программы со статусом 'active' и 'enrolled'
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function activePrograms()
    {
        return $this->programs()->whereIn('user_programs.status', ['active', 'enrolled']);
    }

    /**
     * Получить активные курсы пользователя
     * Включает курсы со статусом 'active' и 'enrolled'
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function activeCourses()
    {
        return $this->courses()->whereIn('user_courses.status', ['active', 'enrolled']);
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

    /**
     * История зачислений/отчислений
     */
    public function enrollmentHistory()
    {
        return $this->hasMany(EnrollmentHistory::class);
    }

    /**
     * Платежи пользователя
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Сертификаты пользователя
     */
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Получить токен Moodle API для пользователя
     * Если у пользователя нет токена, возвращает общий токен из конфига
     * 
     * @return string|null Токен Moodle API или null если не настроен
     */
    public function getMoodleToken(): ?string
    {
        // Сначала проверяем токен пользователя (для преподавателей и администраторов)
        if (!empty($this->moodle_token)) {
            return $this->moodle_token;
        }
        
        // Если у пользователя нет токена, используем общий токен из конфига
        return config('services.moodle.token', '');
    }

    /**
     * Проверить, имеет ли пользователь настроенный токен Moodle
     * 
     * @return bool
     */
    public function hasMoodleToken(): bool
    {
        return !empty($this->moodle_token);
    }

    /**
     * Проверить зависимости перед удалением
     */
    public function checkDependencies(): array
    {
        $dependencies = [];

        // Проверяем курсы, которые преподает пользователь
        $taughtCoursesCount = $this->taughtCourses()->count();
        if ($taughtCoursesCount > 0) {
            $dependencies[] = [
                'name' => 'Курсы (преподаватель)',
                'count' => $taughtCoursesCount,
                'type' => 'courses'
            ];
        }

        // Проверяем платежи
        $paymentsCount = $this->payments()->count();
        if ($paymentsCount > 0) {
            $dependencies[] = [
                'name' => 'Платежи',
                'count' => $paymentsCount,
                'type' => 'payments'
            ];
        }

        // Проверяем сертификаты
        $certificatesCount = $this->certificates()->count();
        if ($certificatesCount > 0) {
            $dependencies[] = [
                'name' => 'Сертификаты',
                'count' => $certificatesCount,
                'type' => 'certificates'
            ];
        }

        return $dependencies;
    }

    /**
     * Получить Moodle User ID из локального ID пользователя
     *
     * @param int $localUserId Локальный ID пользователя в системе
     * @return int|null Moodle User ID или null, если пользователь не найден или не синхронизирован с Moodle
     */
    public static function getMoodleUserId(int $localUserId): ?int
    {
        $user = self::find($localUserId);
        return $user ? $user->moodle_user_id : null;
    }

    /**
     * Проверить, является ли переданный ID Moodle User ID или локальным ID
     * Если это локальный ID, вернуть соответствующий Moodle User ID
     *
     * @param int $userId ID пользователя (может быть как локальным, так и Moodle User ID)
     * @return int|null Moodle User ID или null, если пользователь не найден
     */
    public static function resolveToMoodleUserId(int $userId): ?int
    {
        // Сначала проверяем, является ли это moodle_user_id
        $user = self::where('moodle_user_id', $userId)->first();
        if ($user) {
            return $user->moodle_user_id;
        }

        // Если нет, проверяем как локальный ID
        $user = self::find($userId);
        return $user ? $user->moodle_user_id : null;
    }
}
