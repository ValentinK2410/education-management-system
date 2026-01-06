<?php

namespace App\Models;

use App\Traits\LogsActivity;
use App\Traits\Versionable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Модель платежей
 *
 * Отслеживает платежи за курсы и программы
 */
class Payment extends Model
{
    use HasFactory, SoftDeletes, Versionable, LogsActivity;

    protected $fillable = [
        'user_id',
        'course_id',
        'program_id',
        'entity_type',
        'amount',
        'currency',
        'status',
        'payment_method',
        'transaction_id',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
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
     * Получить название статуса на русском
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'pending' => 'Ожидает оплаты',
            'paid' => 'Оплачено',
            'failed' => 'Ошибка оплаты',
            'refunded' => 'Возврат',
            'cancelled' => 'Отменено',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Область видимости для получения оплаченных платежей
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Область видимости для получения ожидающих оплаты
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
