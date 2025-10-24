<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель отзыва о курсе
 */
class Review extends Model
{
    use HasFactory;

    /**
     * Поля, которые можно массово назначать
     */
    protected $fillable = [
        'user_id',
        'course_id',
        'rating',
        'comment',
        'is_approved',
    ];

    /**
     * Приведение типов атрибутов
     */
    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
    ];

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с курсом
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Scope для одобренных отзывов
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope для отзывов по курсу
     */
    public function scopeForCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Scope для отзывов пользователя
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Получить средний рейтинг курса
     */
    public static function getAverageRatingForCourse($courseId)
    {
        return static::where('course_id', $courseId)
            ->where('is_approved', true)
            ->avg('rating');
    }

    /**
     * Получить количество отзывов для курса
     */
    public static function getReviewsCountForCourse($courseId)
    {
        return static::where('course_id', $courseId)
            ->where('is_approved', true)
            ->count();
    }
}
