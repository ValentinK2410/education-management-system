<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель прогресса студента по элементу курса
 *
 * Отслеживает статус выполнения, оценки и даты для каждого студента и элемента курса
 */
class StudentActivityProgress extends Model
{
    use HasFactory;

    /**
     * Поля, доступные для массового заполнения
     */
    protected $fillable = [
        'user_id',
        'course_id',
        'activity_id',
        'status',
        'is_viewed',
        'is_read',
        'last_viewed_at',
        'view_count',
        'has_draft',
        'draft_created_at',
        'draft_updated_at',
        'draft_data',
        'needs_grading',
        'needs_response',
        'is_graded',
        'grading_requested_at',
        'attempts_count',
        'max_attempts',
        'last_attempt_at',
        'questions_data',
        'correct_answers',
        'total_questions',
        'completion_data',
        'completion_percentage',
        'grade',
        'max_grade',
        'started_at',
        'submitted_at',
        'graded_at',
        'graded_by_user_id',
        'feedback',
        'progress_data',
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам
     */
    protected $casts = [
        'is_viewed' => 'boolean',
        'is_read' => 'boolean',
        'has_draft' => 'boolean',
        'needs_grading' => 'boolean',
        'needs_response' => 'boolean',
        'is_graded' => 'boolean',
        'last_viewed_at' => 'datetime',
        'draft_created_at' => 'datetime',
        'draft_updated_at' => 'datetime',
        'grading_requested_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'draft_data' => 'array',
        'questions_data' => 'array',
        'completion_data' => 'array',
        'completion_percentage' => 'decimal:2',
        'grade' => 'decimal:2',
        'max_grade' => 'decimal:2',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'progress_data' => 'array',
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
     * Получить пользователя, который проверил работу
     *
     * @return BelongsTo
     */
    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by_user_id');
    }

    /**
     * Проверить, завершено ли выполнение элемента
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, ['graded', 'completed']);
    }

    /**
     * Проверить, сдано ли задание
     *
     * @return bool
     */
    public function isSubmitted(): bool
    {
        return in_array($this->status, ['submitted', 'graded', 'completed']);
    }

    /**
     * Получить процент выполнения
     *
     * @return float
     */
    public function getCompletionPercentage(): float
    {
        if (!$this->max_grade || $this->max_grade == 0) {
            return $this->isCompleted() ? 100 : 0;
        }

        if (!$this->grade) {
            return 0;
        }

        return min(100, ($this->grade / $this->max_grade) * 100);
    }
}

