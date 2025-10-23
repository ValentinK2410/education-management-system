<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель учебного заведения
 *
 * Представляет образовательные учреждения в системе.
 * Каждое учебное заведение может иметь множество образовательных программ.
 */
class Institution extends Model
{
    use HasFactory;

    /**
     * Поля, доступные для массового заполнения
     */
    protected $fillable = [
        'name',        // Название учебного заведения
        'description', // Описание
        'address',     // Адрес
        'phone',       // Телефон
        'email',       // Email
        'website',     // Веб-сайт
        'logo',        // Путь к логотипу
        'is_active',   // Статус активности
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам
     */
    protected $casts = [
        'is_active' => 'boolean',  // Статус активности как булево значение
    ];

    /**
     * Получить образовательные программы учебного заведения
     *
     * @return HasMany
     */
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    /**
     * Область видимости для получения только активных учебных заведений
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
