<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Модель истории зачислений/отчислений
 *
 * Отслеживает все изменения статуса обучения пользователей
 */
class EnrollmentHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'enrollment_history';

    protected $fillable = [
        'user_id',
        'course_id',
        'program_id',
        'entity_type',
        'action',
        'old_status',
        'new_status',
        'notes',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    /**
     * Получить пользователя
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить курс
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Получить программу
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Получить пользователя, который изменил статус
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Получить название действия на русском
     */
    public function getActionLabelAttribute(): string
    {
        $labels = [
            'enrolled' => 'Зачислен',
            'activated' => 'Активирован',
            'completed' => 'Завершен',
            'cancelled' => 'Отчислен',
            'reinstated' => 'Восстановлен',
        ];

        return $labels[$this->action] ?? $this->action;
    }

    /**
     * Получить название статуса на русском
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'enrolled' => 'Зачислен',
            'active' => 'Активен',
            'completed' => 'Завершен',
            'cancelled' => 'Отчислен',
        ];

        return $labels[$this->new_status] ?? $this->new_status;
    }
}
