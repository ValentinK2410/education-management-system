<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Модель лога активности
 *
 * Хранит логи всех критических операций в системе
 */
class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'loggable_type',
        'loggable_id',
        'action',
        'user_id',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'description',
        'properties',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'properties' => 'array',
    ];

    /**
     * Получить модель, к которой относится лог
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Получить пользователя, выполнившего действие
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Область видимости для получения логов по типу действия
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Область видимости для получения логов пользователя
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Область видимости для получения логов модели
     */
    public function scopeForModel($query, string $modelType, int $modelId)
    {
        return $query->where('loggable_type', $modelType)
            ->where('loggable_id', $modelId);
    }
}

