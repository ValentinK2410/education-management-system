<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель образовательной программы
 *
 * Представляет образовательную программу в рамках учебного заведения.
 * Каждая программа принадлежит учебному заведению и может содержать множество курсов.
 */
class Program extends Model
{
    use HasFactory;

    /**
     * Поля, доступные для массового заполнения
     */
    protected $fillable = [
        'name',          // Название программы
        'description',   // Описание программы
        'institution_id', // ID учебного заведения
        'duration',      // Продолжительность программы
        'degree_level',  // Уровень степени
        'tuition_fee',   // Стоимость обучения
        'language',      // Язык обучения
        'requirements',  // Требования для поступления
        'is_active',     // Статус активности
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам
     */
    protected $casts = [
        'requirements' => 'array',    // Требования как массив
        'is_active' => 'boolean',     // Статус активности как булево значение
        'tuition_fee' => 'decimal:2', // Стоимость обучения как десятичное число с 2 знаками
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
}
