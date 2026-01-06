<?php

namespace App\Models;

use App\Traits\ChecksDependencies;
use App\Traits\LogsActivity;
use App\Traits\Versionable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Модель учебного заведения
 *
 * Представляет образовательные учреждения в системе.
 * Каждое учебное заведение может иметь множество образовательных программ.
 */
class Institution extends Model
{
    use HasFactory, SoftDeletes, Versionable, ChecksDependencies, LogsActivity;

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

    /**
     * Проверить зависимости перед удалением
     */
    public function checkDependencies(): array
    {
        $dependencies = [];

        // Проверяем программы учебного заведения
        $programsCount = $this->programs()->count();
        if ($programsCount > 0) {
            $dependencies[] = [
                'name' => 'Образовательные программы',
                'count' => $programsCount,
                'type' => 'programs'
            ];
        }

        return $dependencies;
    }
}
