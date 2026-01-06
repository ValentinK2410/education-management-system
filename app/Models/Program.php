<?php

namespace App\Models;

use App\Traits\ChecksDependencies;
use App\Traits\LogsActivity;
use App\Traits\Versionable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Модель образовательной программы
 *
 * Представляет образовательную программу в рамках учебного заведения.
 * Каждая программа принадлежит учебному заведению и может содержать множество курсов.
 */
class Program extends Model
{
    use HasFactory, SoftDeletes, Versionable, ChecksDependencies, LogsActivity;

    public const DEGREE_LEVELS = [
        'начальный' => 'Начальный',
        'бакалаврский' => 'Бакалаврский',
        'магистерский' => 'Магистерский',
    ];

    /**
     * Поля, доступные для массового заполнения
     */
    protected $fillable = [
        'name',          // Название программы
        'code',          // Код программы
        'description',   // Описание программы
        'institution_id', // ID учебного заведения
        'duration',      // Продолжительность программы
        'credits',       // Количество кредитов
        'degree_level',  // Уровень степени
        'tuition_fee',   // Стоимость обучения
        'language',      // Язык обучения
        'location',      // Местоположение программы
        'requirements',  // Требования для поступления
        'is_active',     // Статус активности
        'is_paid',       // Платная программа или бесплатная
        'price',         // Цена программы
        'currency',      // Валюта
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам
     */
    protected $casts = [
        'requirements' => 'array',    // Требования как массив
        'is_active' => 'boolean',     // Статус активности как булево значение
        'is_paid' => 'boolean',       // Платная программа как булево значение
        'tuition_fee' => 'decimal:2', // Стоимость обучения как десятичное число с 2 знаками
        'price' => 'decimal:2',       // Цена как десятичное число
    ];

    /**
     * Получить учебное заведение, которому принадлежит программа
     *
     * @return BelongsTo
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Получить курсы, входящие в программу
     *
     * @return HasMany
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Область видимости для получения только активных программ
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Область видимости для получения только платных программ
     *
     * @param $query
     * @return mixed
     */
    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    /**
     * Область видимости для получения только бесплатных программ
     *
     * @param $query
     * @return mixed
     */
    public function scopeFree($query)
    {
        return $query->where('is_paid', false);
    }

    /**
     * Получить пользователей, записанных на программу
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_programs')
                    ->withPivot(['status', 'enrolled_at', 'completed_at', 'notes'])
                    ->withTimestamps();
    }

    /**
     * Получить количество записанных пользователей
     *
     * @return int
     */
    public function getEnrolledUsersCountAttribute()
    {
        return $this->users()->count();
    }

    /**
     * Получить отформатированный уровень степени
     */
    public function getDegreeLevelLabelAttribute(): ?string
    {
        return $this->degree_level
            ? (self::DEGREE_LEVELS[$this->degree_level] ?? mb_convert_case($this->degree_level, MB_CASE_TITLE, 'UTF-8'))
            : null;
    }

    /**
     * Получить доступные варианты уровня степени
     */
    public static function degreeLevelOptions(): array
    {
        return self::DEGREE_LEVELS;
    }

    /**
     * История зачислений на программу
     */
    public function enrollmentHistory()
    {
        return $this->hasMany(EnrollmentHistory::class);
    }

    /**
     * Платежи за программу
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Сертификаты по программе
     */
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Проверить зависимости перед удалением
     */
    public function checkDependencies(): array
    {
        $dependencies = [];

        // Проверяем курсы в программе
        $coursesCount = $this->courses()->count();
        if ($coursesCount > 0) {
            $dependencies[] = [
                'name' => 'Курсы',
                'count' => $coursesCount,
                'type' => 'courses'
            ];
        }

        // Проверяем записанных пользователей
        $enrolledUsersCount = $this->users()->count();
        if ($enrolledUsersCount > 0) {
            $dependencies[] = [
                'name' => 'Записанные пользователи',
                'count' => $enrolledUsersCount,
                'type' => 'users'
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

        return $dependencies;
    }
}
