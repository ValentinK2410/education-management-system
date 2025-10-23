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
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам
     */
    protected $casts = [
        'prerequisites' => 'array',     // Предварительные требования как массив
        'learning_outcomes' => 'array', // Результаты обучения как массив
        'is_active' => 'boolean',       // Статус активности как булево значение
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
}
