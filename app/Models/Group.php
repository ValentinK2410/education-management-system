<?php

namespace App\Models;

use App\Traits\ChecksDependencies;
use App\Traits\LogsActivity;
use App\Traits\Versionable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Модель группы студентов
 *
 * Представляет группу студентов в системе.
 * Группа может быть связана с курсом или программой.
 */
class Group extends Model
{
    use HasFactory, SoftDeletes, Versionable, ChecksDependencies, LogsActivity;

    /**
     * Поля, доступные для массового заполнения
     */
    protected $fillable = [
        'name',             // Название группы
        'description',       // Описание
        'course_id',        // ID курса (опционально)
        'program_id',       // ID программы (опционально)
        'moodle_cohort_id', // ID глобальной группы в Moodle (опционально)
        'is_active',        // Статус активности
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Получить студентов группы
     *
     * @return BelongsToMany
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_groups')
            ->withPivot(['enrolled_at', 'notes'])
            ->withTimestamps();
    }

    /**
     * Получить курс, к которому привязана группа
     *
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Получить программу, к которой привязана группа
     *
     * @return BelongsTo
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Область видимости для получения только активных групп
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

        // Проверяем студентов в группе
        $studentsCount = $this->students()->count();
        if ($studentsCount > 0) {
            $dependencies[] = [
                'name' => 'Студенты в группе',
                'count' => $studentsCount,
                'type' => 'students'
            ];
        }

        return $dependencies;
    }
}
