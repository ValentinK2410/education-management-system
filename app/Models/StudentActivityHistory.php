<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель истории действий студентов
 *
 * Хранит историю всех действий студентов по элементам курса
 */
class StudentActivityHistory extends Model
{
    use HasFactory;

    /**
     * Отключить автоматическое управление timestamps
     * Используем только created_at
     */
    public $timestamps = false;

    /**
     * Поля, доступные для массового заполнения
     */
    protected $fillable = [
        'user_id',
        'course_id',
        'activity_id',
        'action_type',
        'action_data',
        'performed_by_user_id',
        'description',
        'created_at',
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам
     */
    protected $casts = [
        'action_data' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Получить студента
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить курс
     *
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Получить элемент курса
     *
     * @return BelongsTo
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(CourseActivity::class, 'activity_id');
    }

    /**
     * Получить пользователя, который выполнил действие
     *
     * @return BelongsTo
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }

    /**
     * Scope для фильтрации по типу действия
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $actionType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    /**
     * Scope для фильтрации по дате
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $from
     * @param string $to
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}

