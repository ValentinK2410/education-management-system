<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель элемента курса
 *
 * Представляет элемент курса из Moodle (задание, тест, форум, материал)
 */
class CourseActivity extends Model
{
    use HasFactory;

    /**
     * Поля, доступные для массового заполнения
     */
    protected $fillable = [
        'course_id',
        'moodle_activity_id',
        'activity_type',
        'name',
        'section_name',
        'moodle_section_id',
        'week_number',
        'section_number',
        'section_order',
        'section_type',
        'max_grade',
        'due_date',
        'description',
        'meta',
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам
     */
    protected $casts = [
        'max_grade' => 'decimal:2',
        'due_date' => 'datetime',
        'meta' => 'array',
    ];

    /**
     * Получить курс, к которому принадлежит элемент
     *
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Получить прогресс студентов по этому элементу
     *
     * @return HasMany
     */
    public function studentProgress(): HasMany
    {
        return $this->hasMany(StudentActivityProgress::class, 'activity_id');
    }

    /**
     * Получить историю действий студентов по этому элементу
     *
     * @return HasMany
     */
    public function studentHistory(): HasMany
    {
        return $this->hasMany(StudentActivityHistory::class, 'activity_id');
    }

    /**
     * Получить прогресс конкретного студента по этому элементу
     *
     * @param int $userId ID студента
     * @return StudentActivityProgress|null
     */
    public function getStudentProgress(int $userId): ?StudentActivityProgress
    {
        return $this->studentProgress()
            ->where('user_id', $userId)
            ->where('course_id', $this->course_id)
            ->first();
    }

    /**
     * Получить Course Module ID (cmid) из метаданных
     *
     * @return int|null
     */
    public function getCmidAttribute(): ?int
    {
        try {
            // Проверяем, есть ли поле cmid в таблице
            if (isset($this->attributes['cmid'])) {
                $cmid = $this->attributes['cmid'];
                return $cmid ? (int)$cmid : null;
            }

            // Если нет, пытаемся получить из meta (только реальный cmid, не moodle_id)
            $meta = $this->meta;
            if ($meta && is_array($meta)) {
                // Используем только cmid из meta, не используем moodle_id (это instance ID, а не cmid)
                if (isset($meta['cmid']) && $meta['cmid']) {
                    return (int)$meta['cmid'];
                }
            }

            return null;
        } catch (\Exception $e) {
            \Log::warning('Ошибка при получении cmid для элемента курса', [
                'activity_id' => $this->id ?? null,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Получить URL для перехода к элементу в Moodle
     *
     * @return string|null
     */
    public function getMoodleUrlAttribute(): ?string
    {
        try {
            $cmid = $this->cmid;
            if (!$cmid) {
                return null;
            }

            // Получаем курс безопасным способом
            try {
                $course = $this->course;
            } catch (\Exception $e) {
                // Если связь не загружена, пытаемся загрузить
                $course = $this->course()->first();
            }

            if (!$course || !$course->moodle_course_id) {
                return null;
            }

            $moodleUrl = config('services.moodle.url');
            if (!$moodleUrl) {
                return null;
            }

            $moodleUrl = rtrim($moodleUrl, '/');

            // Формируем URL в зависимости от типа элемента
            switch ($this->activity_type) {
                case 'assign':
                    return $moodleUrl . "/mod/assign/view.php?id={$cmid}";
                case 'quiz':
                    // Для тестов направляем на страницу отчета для просмотра ответов
                    return $moodleUrl . "/mod/quiz/report.php?id={$cmid}&mode=overview";
                case 'forum':
                    return $moodleUrl . "/mod/forum/view.php?id={$cmid}";
                case 'resource':
                case 'file':
                case 'url':
                case 'page':
                case 'book':
                    return $moodleUrl . "/mod/{$this->activity_type}/view.php?id={$cmid}";
                case 'folder':
                    return $moodleUrl . "/mod/folder/view.php?id={$cmid}";
                default:
                    // Для других типов - общая ссылка на курс
                    return $moodleUrl . "/course/view.php?id={$course->moodle_course_id}";
            }
        } catch (\Exception $e) {
            // В случае ошибки возвращаем null вместо исключения
            \Log::warning('Ошибка при формировании URL Moodle для элемента курса', [
                'activity_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}

