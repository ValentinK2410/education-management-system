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
}
