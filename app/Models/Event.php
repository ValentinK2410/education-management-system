<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Модель события
 */
class Event extends Model
{
    use HasFactory;

    /**
     * Поля, которые можно массово назначать
     */
    protected $fillable = [
        'title',
        'description',
        'content',
        'start_date',
        'end_date',
        'location',
        'image',
        'is_published',
        'is_featured',
        'max_participants',
        'price',
        'currency',
        'registration_url',
    ];

    /**
     * Приведение типов атрибутов
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'max_participants' => 'integer',
        'price' => 'decimal:2',
    ];

    /**
     * Scope для опубликованных событий
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope для рекомендуемых событий
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope для будущих событий
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    /**
     * Scope для прошедших событий
     */
    public function scopePast($query)
    {
        return $query->where('start_date', '<', now());
    }

    /**
     * Scope для событий в определенном периоде
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate]);
    }

    /**
     * Проверить, является ли событие бесплатным
     */
    public function isFree(): bool
    {
        return is_null($this->price) || $this->price == 0;
    }

    /**
     * Получить отформатированную цену
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->isFree()) {
            return 'Бесплатно';
        }

        return number_format($this->price, 0, ',', ' ') . ' ' . $this->currency;
    }

    /**
     * Получить статус события
     */
    public function getStatusAttribute(): string
    {
        $now = now();
        
        if ($this->start_date > $now) {
            return 'upcoming';
        } elseif ($this->end_date && $this->end_date < $now) {
            return 'past';
        } else {
            return 'ongoing';
        }
    }

    /**
     * Получить количество дней до события
     */
    public function getDaysUntilEventAttribute(): int
    {
        return now()->diffInDays($this->start_date, false);
    }
}
