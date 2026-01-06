<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Trait для логирования активности моделей
 *
 * Автоматически логирует создание, обновление и удаление записей
 */
trait LogsActivity
{
    /**
     * Boot the trait
     */
    public static function bootLogsActivity(): void
    {
        // Логируем создание
        static::created(function ($model) {
            $model->logActivity('created', null, $model->getAttributes());
        });

        // Логируем обновление
        static::updating(function ($model) {
            $model->logActivity('updated', $model->getOriginal(), $model->getAttributes());
        });

        // Логируем удаление (soft delete)
        static::deleting(function ($model) {
            if (method_exists($model, 'getDeletedAtColumn')) {
                $model->logActivity('deleted', $model->getAttributes(), null);
            } else {
                $model->logActivity('deleted', $model->getAttributes(), null);
            }
        });

        // Логируем восстановление (soft delete restore)
        static::restored(function ($model) {
            $model->logActivity('restored', null, $model->getAttributes());
        });
    }

    /**
     * Логировать активность
     */
    public function logActivity(
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): ActivityLog {
        return ActivityLog::create([
            'loggable_type' => get_class($this),
            'loggable_id' => $this->getKey(),
            'action' => $action,
            'user_id' => Auth::id(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description ?: $this->getActivityDescription($action),
            'properties' => $this->getActivityProperties(),
        ]);
    }

    /**
     * Получить описание активности
     */
    protected function getActivityDescription(string $action): string
    {
        $modelName = class_basename($this);
        
        $descriptions = [
            'created' => "Создана запись {$modelName}",
            'updated' => "Обновлена запись {$modelName}",
            'deleted' => "Удалена запись {$modelName}",
            'restored' => "Восстановлена запись {$modelName}",
        ];

        return $descriptions[$action] ?? "Действие {$action} для {$modelName}";
    }

    /**
     * Получить дополнительные свойства для логирования
     */
    protected function getActivityProperties(): array
    {
        return [];
    }

    /**
     * Получить все логи активности для этой модели
     */
    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable')
            ->orderBy('created_at', 'desc');
    }
}

