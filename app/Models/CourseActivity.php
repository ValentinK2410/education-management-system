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
     * Кэш для moodle_url, чтобы избежать повторных вычислений
     *
     * @var array
     */
    protected static $moodleUrlCache = [];

    /**
     * Получить URL для перехода к элементу в Moodle
     *
     * @return string|null
     */
    public function getMoodleUrlAttribute(): ?string
    {
        // Используем кэш для избежания повторных вычислений
        $cacheKey = $this->id ?? 'unknown';
        if (isset(self::$moodleUrlCache[$cacheKey])) {
            return self::$moodleUrlCache[$cacheKey];
        }

        try {
            $cmid = $this->cmid;

            // Получаем курс безопасным способом
            $course = null;
            try {
                // Сначала пытаемся получить через прямую проверку relations (работает с setRelation)
                if (isset($this->relations['course']) && $this->relations['course'] !== null) {
                    $course = $this->relations['course'];
                } elseif (method_exists($this, 'getRelation') && $this->relationLoaded('course')) {
                    // Используем relationLoaded для проверки, затем getRelation
                    $course = $this->getRelation('course');
                } elseif ($this->course_id) {
                    // Если связь не загружена, но есть course_id, загружаем её
                    // Это fallback для случаев, когда связь не была предзагружена
                    $course = $this->course;
                } else {
                    // Если связь не загружена и нет course_id, возвращаем null
                    self::$moodleUrlCache[$cacheKey] = null;
                    return null;
                }
            } catch (\Exception $e) {
                // В случае ошибки возвращаем null без дополнительных запросов
                self::$moodleUrlCache[$cacheKey] = null;
                return null;
            }

            if (!$course || !$course->moodle_course_id) {
                // Не логируем каждый раз - это создает слишком много записей
                self::$moodleUrlCache[$cacheKey] = null;
                return null;
            }

            $moodleUrl = config('services.moodle.url');
            if (!$moodleUrl) {
                self::$moodleUrlCache[$cacheKey] = null;
                return null;
            }

            $moodleUrl = rtrim($moodleUrl, '/');

            // Если cmid отсутствует, просто возвращаем null
            if (!$cmid) {
                // Не логируем каждый раз - это создает слишком много записей
                self::$moodleUrlCache[$cacheKey] = null;
                return null;
            }

            // Формируем URL в зависимости от типа элемента
            $url = null;
            switch ($this->activity_type) {
                case 'assign':
                    $url = $moodleUrl . "/mod/assign/view.php?id={$cmid}";
                    break;
                case 'quiz':
                    // Для тестов направляем на страницу отчета для просмотра ответов
                    $url = $moodleUrl . "/mod/quiz/report.php?id={$cmid}&mode=overview";
                    break;
                case 'forum':
                    $url = $moodleUrl . "/mod/forum/view.php?id={$cmid}";
                    break;
                case 'resource':
                case 'file':
                case 'url':
                case 'page':
                case 'book':
                    $url = $moodleUrl . "/mod/{$this->activity_type}/view.php?id={$cmid}";
                    break;
                case 'folder':
                    $url = $moodleUrl . "/mod/folder/view.php?id={$cmid}";
                    break;
                default:
                    // Для других типов - общая ссылка на курс
                    $url = $moodleUrl . "/course/view.php?id={$course->moodle_course_id}";
                    break;
            }

            // Сохраняем в кэш
            self::$moodleUrlCache[$cacheKey] = $url;
            return $url;
        } catch (\Exception $e) {
            // В случае ошибки возвращаем null вместо исключения
            // Не логируем каждый вызов - это создает слишком много записей и может привести к исчерпанию памяти
            self::$moodleUrlCache[$cacheKey] = null;
            return null;
        }
    }

    /**
     * Проверить, есть ли колонка в таблице
     */
    protected function hasColumn(string $column): bool
    {
        return \Schema::hasColumn($this->getTable(), $column);
    }
}

