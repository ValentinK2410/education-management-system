<?php

namespace App\Models;

use App\Traits\ChecksDependencies;
use App\Traits\LogsActivity;
use App\Traits\Versionable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Модель курса
 *
 * Представляет отдельный курс в рамках образовательной программы.
 * Каждый курс принадлежит программе и может иметь преподавателя.
 */
class Course extends Model
{
    use HasFactory, SoftDeletes, Versionable, ChecksDependencies, LogsActivity;

    /**
     * Поля, доступные для массового заполнения
     */
    protected $fillable = [
        'name',              // Название курса
        'description',       // Описание курса
        'short_description', // Краткое описание
        'image',             // Изображение обложки курса
        'program_id',        // ID образовательной программы (для обратной совместимости)
        'subject_id',        // ID предмета (глобального курса)
        'order',             // Порядок курса в программе
        'instructor_id',     // ID преподавателя
        'code',              // Код курса
        'credits',           // Количество кредитов
        'duration',          // Продолжительность курса
        'schedule',          // Расписание
        'location',          // Место проведения
        'prerequisites',     // Предварительные требования
        'learning_outcomes', // Результаты обучения
        'is_active',         // Статус активности
        'is_paid',           // Платный курс или бесплатный
        'price',             // Цена курса
        'currency',          // Валюта
        'wordpress_course_id', // ID курса в WordPress
        'moodle_course_id',   // ID курса в Moodle
        'category_id',       // ID категории из Moodle
        'category_name',     // Название категории
        'start_date',         // Дата начала курса
        'end_date',           // Дата окончания курса
        'capacity',           // Вместимость курса
        'enrolled',           // Количество записанных студентов
        'meta',               // Дополнительные метаданные (JSON)
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам
     */
    protected $casts = [
        'prerequisites' => 'array',     // Предварительные требования как массив
        'learning_outcomes' => 'array', // Результаты обучения как массив
        'is_active' => 'boolean',       // Статус активности как булево значение
        'is_paid' => 'boolean',         // Платный курс как булево значение
        'price' => 'decimal:2',         // Цена как десятичное число
        'start_date' => 'date',         // Дата начала как дата
        'end_date' => 'date',           // Дата окончания как дата
        'meta' => 'array',              // Метаданные как массив
    ];

    /**
     * Получить образовательную программу, к которой принадлежит курс
     *
     * @return BelongsTo
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Получить предмет (глобальный курс), к которому принадлежит курс
     *
     * @return BelongsTo
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Получить преподавателя, который ведет курс
     *
     * @return BelongsTo
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Получить всех преподавателей курса (например, из Moodle)
     *
     * @return BelongsToMany
     */
    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_instructors')
            ->withPivot(['source', 'moodle_role_shortname', 'is_primary'])
            ->withTimestamps();
    }

    /**
     * Область видимости для получения только активных курсов
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Область видимости для получения только платных курсов
     *
     * @param $query
     * @return mixed
     */
    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    /**
     * Область видимости для получения только бесплатных курсов
     *
     * @param $query
     * @return mixed
     */
    public function scopeFree($query)
    {
        return $query->where('is_paid', false);
    }

    /**
     * Получить пользователей, записанных на курс
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_courses')
                    ->withPivot(['status', 'enrolled_at', 'completed_at', 'progress', 'notes'])
                    ->withTimestamps();
    }

    /**
     * Получить количество записанных пользователей
     *
     * @return int
     */
    public function getEnrolledUsersCountAttribute()
    {
        return $this->users()->count();
    }

    /**
     * Отзывы курса
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Одобренные отзывы курса
     */
    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    /**
     * Получить средний рейтинг курса
     */
    public function getAverageRatingAttribute()
    {
        return $this->approvedReviews()->avg('rating');
    }

    /**
     * Получить количество отзывов
     */
    public function getReviewsCountAttribute()
    {
        return $this->approvedReviews()->count();
    }

    /**
     * Получить элементы курса (activities)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activities()
    {
        return $this->hasMany(CourseActivity::class);
    }

    /**
     * Получить прогресс студентов по курсу
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function studentActivityProgress()
    {
        return $this->hasMany(StudentActivityProgress::class);
    }

    /**
     * Получить историю действий студентов по курсу
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function studentActivityHistory()
    {
        return $this->hasMany(StudentActivityHistory::class);
    }

    /**
     * Получить URL изображения обложки курса
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }

        // Возвращаем дефолтное изображение на основе названия курса
        return $this->getDefaultImageUrl();
    }

    /**
     * Получить URL дефолтного изображения на основе названия курса
     *
     * @return string
     */
    private function getDefaultImageUrl()
    {
        // Генерируем градиент на основе названия курса
        $colors = [
            ['#667eea', '#764ba2'], // Фиолетовый
            ['#f093fb', '#f5576c'], // Розовый
            ['#4facfe', '#00f2fe'], // Синий
            ['#43e97b', '#38f9d7'], // Зеленый
            ['#fa709a', '#fee140'], // Оранжевый
            ['#30cfd0', '#330867'], // Бирюзовый
            ['#a8edea', '#fed6e3'], // Светлый
            ['#ff9a9e', '#fecfef'], // Розово-красный
        ];

        $index = crc32($this->name) % count($colors);
        $gradient = $colors[$index];

        // Возвращаем CSS градиент как data URI
        return "linear-gradient(135deg, {$gradient[0]} 0%, {$gradient[1]} 100%)";
    }

    /**
     * История зачислений на курс
     */
    public function enrollmentHistory()
    {
        return $this->hasMany(EnrollmentHistory::class);
    }

    /**
     * Платежи за курс
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Сертификаты по курсу
     */
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Проверить зависимости перед удалением
     */
    public function checkDependencies(): array
    {
        $dependencies = [];

        // Проверяем записанных пользователей
        $enrolledUsersCount = $this->users()->count();
        if ($enrolledUsersCount > 0) {
            $dependencies[] = [
                'name' => 'Записанные пользователи',
                'count' => $enrolledUsersCount,
                'type' => 'users'
            ];
        }

        // Проверяем платежи
        $paymentsCount = $this->payments()->count();
        if ($paymentsCount > 0) {
            $dependencies[] = [
                'name' => 'Платежи',
                'count' => $paymentsCount,
                'type' => 'payments'
            ];
        }

        // Проверяем отзывы
        $reviewsCount = $this->reviews()->count();
        if ($reviewsCount > 0) {
            $dependencies[] = [
                'name' => 'Отзывы',
                'count' => $reviewsCount,
                'type' => 'reviews'
            ];
        }

        // Проверяем сертификаты
        $certificatesCount = $this->certificates()->count();
        if ($certificatesCount > 0) {
            $dependencies[] = [
                'name' => 'Сертификаты',
                'count' => $certificatesCount,
                'type' => 'certificates'
            ];
        }

        return $dependencies;
    }

    /**
     * Получить Moodle Course ID из локального ID курса
     *
     * @param int $localCourseId Локальный ID курса в системе
     * @return int|null Moodle Course ID или null, если курс не найден или не синхронизирован с Moodle
     */
    public static function getMoodleCourseId(int $localCourseId): ?int
    {
        $course = self::find($localCourseId);
        return $course ? $course->moodle_course_id : null;
    }

    /**
     * Проверить, является ли переданный ID Moodle Course ID или локальным ID
     * Если это локальный ID, вернуть соответствующий Moodle Course ID
     *
     * @param int $courseId ID курса (может быть как локальным, так и Moodle Course ID)
     * @return int|null Moodle Course ID или null, если курс не найден
     */
    public static function resolveToMoodleCourseId(int $courseId): ?int
    {
        // Сначала проверяем, является ли это moodle_course_id
        $course = self::where('moodle_course_id', $courseId)->first();
        if ($course) {
            return $course->moodle_course_id;
        }

        // Если нет, проверяем как локальный ID
        $course = self::find($courseId);
        return $course ? $course->moodle_course_id : null;
    }
}
