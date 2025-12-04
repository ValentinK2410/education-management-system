<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель курса
 *
 * Представляет отдельный курс в рамках образовательной программы.
 * Каждый курс принадлежит программе и может иметь преподавателя.
 */
class Course extends Model
{
    use HasFactory;

    /**
     * Поля, доступные для массового заполнения
     */
    protected $fillable = [
        'name',              // Название курса
        'description',       // Описание курса
        'image',             // Изображение обложки курса
        'program_id',        // ID образовательной программы
        'instructor_id',     // ID преподавателя
        'code',              // Код курса
        'credits',           // Количество кредитов
        'duration',          // Продолжительность курса
        'schedule',          // Расписание
        'location',          // Место проведения
        'prerequisites',     // Предварительные требования
        'learning_outcomes', // Результаты обучения
        'is_active',         // Статус активности
        'is_paid',           // Платный курс или бесплатный
        'price',             // Цена курса
        'currency',          // Валюта
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам
     */
    protected $casts = [
        'prerequisites' => 'array',     // Предварительные требования как массив
        'learning_outcomes' => 'array', // Результаты обучения как массив
        'is_active' => 'boolean',       // Статус активности как булево значение
        'is_paid' => 'boolean',         // Платный курс как булево значение
        'price' => 'decimal:2',         // Цена как десятичное число
    ];

    /**
     * Получить образовательную программу, к которой принадлежит курс
     *
     * @return BelongsTo
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Получить преподавателя, который ведет курс
     *
     * @return BelongsTo
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Область видимости для получения только активных курсов
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Область видимости для получения только платных курсов
     *
     * @param $query
     * @return mixed
     */
    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    /**
     * Область видимости для получения только бесплатных курсов
     *
     * @param $query
     * @return mixed
     */
    public function scopeFree($query)
    {
        return $query->where('is_paid', false);
    }

    /**
     * Получить пользователей, записанных на курс
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_courses')
                    ->withPivot(['status', 'enrolled_at', 'completed_at', 'progress', 'notes'])
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
     * Отзывы курса
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Одобренные отзывы курса
     */
    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    /**
     * Получить средний рейтинг курса
     */
    public function getAverageRatingAttribute()
    {
        return $this->approvedReviews()->avg('rating');
    }

    /**
     * Получить количество отзывов
     */
    public function getReviewsCountAttribute()
    {
        return $this->approvedReviews()->count();
    }

    /**
     * Получить URL изображения обложки курса
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }

        // Возвращаем дефолтное изображение на основе названия курса
        return $this->getDefaultImageUrl();
    }

    /**
     * Получить URL дефолтного изображения на основе названия курса
     *
     * @return string
     */
    private function getDefaultImageUrl()
    {
        // Генерируем градиент на основе названия курса
        $colors = [
            ['#667eea', '#764ba2'], // Фиолетовый
            ['#f093fb', '#f5576c'], // Розовый
            ['#4facfe', '#00f2fe'], // Синий
            ['#43e97b', '#38f9d7'], // Зеленый
            ['#fa709a', '#fee140'], // Оранжевый
            ['#30cfd0', '#330867'], // Бирюзовый
            ['#a8edea', '#fed6e3'], // Светлый
            ['#ff9a9e', '#fecfef'], // Розово-красный
        ];

        $index = crc32($this->name) % count($colors);
        $gradient = $colors[$index];

        // Возвращаем CSS градиент как data URI
        return "linear-gradient(135deg, {$gradient[0]} 0%, {$gradient[1]} 100%)";
    }
}
