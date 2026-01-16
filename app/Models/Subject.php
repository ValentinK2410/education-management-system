<?php

namespace App\Models;

use App\Traits\ChecksDependencies;
use App\Traits\LogsActivity;
use App\Traits\Versionable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Модель предмета (глобального курса)
 *
 * Представляет предмет, который объединяет несколько курсов одной тематики.
 * Предмет может входить в несколько программ и содержать множество курсов.
 */
class Subject extends Model
{
    use HasFactory, SoftDeletes, Versionable, ChecksDependencies, LogsActivity;

    /**
     * Поля, доступные для массового заполнения
     */
    protected $fillable = [
        'name',              // Название предмета
        'description',       // Описание предмета
        'code',              // Код предмета
        'short_description', // Краткое описание
        'image',             // Изображение предмета
        'order',             // Порядок отображения
        'is_active',         // Статус активности
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам
     */
    protected $casts = [
        'is_active' => 'boolean',       // Статус активности как булево значение
        'order' => 'integer',           // Порядок как целое число
    ];

    /**
     * Получить курсы, входящие в предмет
     *
     * @return HasMany
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Получить программы, в которые входит предмет
     *
     * @return BelongsToMany
     */
    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'program_subject')
                    ->withPivot('order')
                    ->withTimestamps()
                    ->orderBy('program_subject.order');
    }

    /**
     * Область видимости для получения только активных предметов
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Область видимости для сортировки по порядку
     *
     * @param $query
     * @return mixed
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Проверить зависимости перед удалением
     */
    public function checkDependencies(): array
    {
        $dependencies = [];

        // Проверяем курсы в предмете
        $coursesCount = $this->courses()->count();
        if ($coursesCount > 0) {
            $dependencies[] = [
                'name' => 'Курсы',
                'count' => $coursesCount,
                'type' => 'courses'
            ];
        }

        // Проверяем программы, в которые входит предмет
        $programsCount = $this->programs()->count();
        if ($programsCount > 0) {
            $dependencies[] = [
                'name' => 'Программы',
                'count' => $programsCount,
                'type' => 'programs'
            ];
        }

        return $dependencies;
    }
}
