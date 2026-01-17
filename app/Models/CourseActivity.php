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

            // Получаем курс безопасным способом
            $course = null;
            try {
                // Сначала пытаемся получить через свойство (если связь загружена)
                if (property_exists($this, 'relations') && isset($this->relations['course'])) {
                    $course = $this->relations['course'];
                } else {
                    // Если связь не загружена, пытаемся загрузить напрямую через ID
                    if ($this->course_id) {
                        $course = \App\Models\Course::find($this->course_id);
                    }
                }
            } catch (\Exception $e) {
                // Если произошла ошибка, логируем и пытаемся загрузить напрямую
                \Log::debug('Ошибка при получении курса для активности', [
                    'activity_id' => $this->id ?? null,
                    'course_id' => $this->course_id ?? null,
                    'error' => $e->getMessage()
                ]);
                // Пытаемся загрузить напрямую через курс
                try {
                    if ($this->course_id) {
                        $course = \App\Models\Course::find($this->course_id);
                    }
                } catch (\Exception $e2) {
                    \Log::warning('Не удалось загрузить курс для активности', [
                        'activity_id' => $this->id ?? null,
                        'course_id' => $this->course_id ?? null,
                        'error' => $e2->getMessage()
                    ]);
                }
            }

            if (!$course || !$course->moodle_course_id) {
                \Log::debug('Не удалось получить URL Moodle: отсутствует курс или moodle_course_id', [
                    'activity_id' => $this->id,
                    'activity_type' => $this->activity_type,
                    'activity_name' => $this->name,
                    'course_id' => $this->course_id,
                    'has_course' => $course ? true : false,
                    'moodle_course_id' => $course->moodle_course_id ?? null
                ]);
                return null;
            }

            $moodleUrl = config('services.moodle.url');
            if (!$moodleUrl) {
                \Log::debug('Не удалось получить URL Moodle: не настроен MOODLE_URL', [
                    'activity_id' => $this->id,
                    'activity_type' => $this->activity_type
                ]);
                return null;
            }

            $moodleUrl = rtrim($moodleUrl, '/');

            // Если cmid отсутствует, НЕ пытаемся получить его через API в представлении
            // Это может привести к множественным запросам и таймаутам
            // Вместо этого cmid должен быть получен при синхронизации из Moodle
            // Если cmid отсутствует, просто возвращаем null

            if (!$cmid) {
                \Log::debug('Не удалось получить URL Moodle: отсутствует cmid', [
                    'activity_id' => $this->id,
                    'activity_type' => $this->activity_type,
                    'activity_name' => $this->name,
                    'moodle_activity_id' => $this->moodle_activity_id,
                    'has_cmid_in_attributes' => isset($this->attributes['cmid']),
                    'has_cmid_in_meta' => isset($this->meta['cmid']) ?? false
                ]);
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
            // Логируем только критичные ошибки, не каждый вызов
            if (strpos($e->getMessage(), 'memory') === false) {
                \Log::warning('Ошибка при формировании URL Moodle для элемента курса', [
                    'activity_id' => $this->id ?? null,
                    'activity_type' => $this->activity_type ?? null,
                    'error' => $e->getMessage()
                ]);
            }
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

