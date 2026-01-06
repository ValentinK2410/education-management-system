<?php

namespace App\Traits;

use App\Models\DataVersion;
use Illuminate\Support\Facades\Auth;

/**
 * Trait для версионирования моделей
 *
 * Автоматически сохраняет версии записей перед обновлением
 */
trait Versionable
{
    /**
     * Boot the trait
     */
    public static function bootVersionable(): void
    {
        // Сохраняем версию перед обновлением
        static::updating(function ($model) {
            $model->saveVersion('updated');
        });

        // Сохраняем версию при создании
        static::created(function ($model) {
            $model->saveVersion('created');
        });

        // Сохраняем версию при удалении (soft delete)
        static::deleting(function ($model) {
            if (method_exists($model, 'getDeletedAtColumn')) {
                $model->saveVersion('deleted');
            }
        });
    }

    /**
     * Сохранить версию записи
     */
    public function saveVersion(string $action = 'updated', ?string $notes = null): DataVersion
    {
        // Получаем все атрибуты модели
        $data = $this->getAttributes();

        // Сохраняем версию
        return DataVersion::create([
            'versionable_type' => get_class($this),
            'versionable_id' => $this->getKey(),
            'data' => $data,
            'created_by' => Auth::id(),
            'action' => $action,
            'notes' => $notes,
        ]);
    }

    /**
     * Получить все версии записи
     */
    public function versions()
    {
        return $this->morphMany(DataVersion::class, 'versionable')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Получить последнюю версию
     */
    public function latestVersion(): ?DataVersion
    {
        return $this->versions()->first();
    }

    /**
     * Восстановить из конкретной версии
     */
    public function restoreFromVersion(int $versionId): bool
    {
        $version = DataVersion::find($versionId);
        
        if (!$version || $version->versionable_id !== $this->getKey()) {
            return false;
        }

        return $version->restore();
    }

    /**
     * Откатиться к предыдущей версии
     */
    public function rollbackToPrevious(): bool
    {
        $previousVersion = $this->versions()->skip(1)->first();
        
        if (!$previousVersion) {
            return false;
        }

        // Сохраняем текущую версию перед откатом
        $this->saveVersion('rollback', 'Откат к предыдущей версии');

        return $previousVersion->restore();
    }
}

