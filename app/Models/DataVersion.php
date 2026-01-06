<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Модель версии данных
 *
 * Хранит историю изменений записей для возможности отката
 */
class DataVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'versionable_type',
        'versionable_id',
        'data',
        'created_by',
        'action',
        'notes',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Получить модель, к которой относится версия
     */
    public function versionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Получить пользователя, создавшего версию
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Восстановить запись из этой версии
     */
    public function restore(): bool
    {
        $model = $this->versionable;
        
        if (!$model) {
            return false;
        }

        // Восстанавливаем данные из версии
        foreach ($this->data as $key => $value) {
            // Пропускаем служебные поля
            if (!in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                $model->$key = $value;
            }
        }

        return $model->save();
    }
}

