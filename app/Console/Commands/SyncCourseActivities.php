<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CourseActivitySyncService;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Команда для синхронизации элементов курса и прогресса студентов из Moodle
 * 
 * Использование:
 * php artisan course-activities:sync                    # Синхронизация всех курсов и студентов
 * php artisan course-activities:sync --course-id=5     # Синхронизация конкретного курса
 * php artisan course-activities:sync --user-id=10       # Синхронизация конкретного студента
 * php artisan course-activities:sync --course-id=5 --user-id=10  # Синхронизация конкретного курса и студента
 */
class SyncCourseActivities extends Command
{
    /**
     * Название и сигнатура консольной команды
     *
     * @var string
     */
    protected $signature = 'course-activities:sync 
                            {--course-id= : ID курса для синхронизации}
                            {--user-id= : ID студента для синхронизации}';

    /**
     * Описание консольной команды
     *
     * @var string
     */
    protected $description = 'Синхронизация элементов курса и прогресса студентов из Moodle';

    /**
     * Сервис синхронизации элементов курса
     *
     * @var CourseActivitySyncService
     */
    protected CourseActivitySyncService $syncService;

    /**
     * Выполнить консольную команду
     *
     * @return int
     */
    public function handle(): int
    {
        try {
            $this->syncService = new CourseActivitySyncService();
        } catch (\Exception $e) {
            $this->error('Ошибка инициализации сервиса синхронизации: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $courseId = $this->option('course-id');
        $userId = $this->option('user-id');

        // Если указаны оба параметра - синхронизируем конкретный курс и студента
        if ($courseId && $userId) {
            return $this->syncCourseAndStudent($courseId, $userId);
        }

        // Если указан только курс - синхронизируем элементы курса и всех студентов
        if ($courseId) {
            return $this->syncCourse($courseId);
        }

        // Если указан только студент - синхронизируем прогресс по всем курсам
        if ($userId) {
            return $this->syncStudent($userId);
        }

        // Если параметры не указаны - полная синхронизация
        return $this->syncAll();
    }

    /**
     * Синхронизировать конкретный курс и студента
     *
     * @param int $courseId
     * @param int $userId
     * @return int
     */
    protected function syncCourseAndStudent(int $courseId, int $userId): int
    {
        $this->info("Синхронизация курса ID {$courseId} и студента ID {$userId}...");

        // Синхронизируем элементы курса
        $this->info("Синхронизация элементов курса...");
        $activityStats = $this->syncService->syncCourseActivities($courseId);
        $this->displayActivityStats($activityStats);

        // Синхронизируем прогресс студента
        $this->info("Синхронизация прогресса студента...");
        $progressStats = $this->syncService->syncStudentProgress($courseId, $userId);
        $this->displayProgressStats($progressStats);

        $this->info("Синхронизация завершена успешно!");
        return Command::SUCCESS;
    }

    /**
     * Синхронизировать конкретный курс
     *
     * @param int $courseId
     * @return int
     */
    protected function syncCourse(int $courseId): int
    {
        $course = Course::find($courseId);
        
        if (!$course) {
            $this->error("Курс с ID {$courseId} не найден");
            return Command::FAILURE;
        }

        $this->info("Синхронизация курса: {$course->name} (ID: {$courseId})...");

        // Синхронизируем элементы курса
        $this->info("Синхронизация элементов курса...");
        $activityStats = $this->syncService->syncCourseActivities($courseId);
        $this->displayActivityStats($activityStats);

        // Синхронизируем прогресс всех студентов курса
        $students = $course->users()->whereNotNull('moodle_user_id')->get();
        $this->info("Найдено студентов для синхронизации: " . $students->count());

        $totalProgress = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
        ];

        foreach ($students as $student) {
            $this->info("Синхронизация прогресса студента: {$student->name} (ID: {$student->id})...");
            $progressStats = $this->syncService->syncStudentProgress($courseId, $student->id);
            
            $totalProgress['total'] += $progressStats['total'];
            $totalProgress['created'] += $progressStats['created'];
            $totalProgress['updated'] += $progressStats['updated'];
            $totalProgress['errors'] += $progressStats['errors'];
        }

        $this->displayProgressStats($totalProgress);
        $this->info("Синхронизация курса завершена!");
        return Command::SUCCESS;
    }

    /**
     * Синхронизировать конкретного студента
     *
     * @param int $userId
     * @return int
     */
    protected function syncStudent(int $userId): int
    {
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("Пользователь с ID {$userId} не найден");
            return Command::FAILURE;
        }

        if (!$user->moodle_user_id) {
            $this->error("У пользователя {$user->name} отсутствует moodle_user_id");
            return Command::FAILURE;
        }

        $this->info("Синхронизация студента: {$user->name} (ID: {$userId})...");

        $courses = $user->courses()->whereNotNull('moodle_course_id')->get();
        $this->info("Найдено курсов: " . $courses->count());

        $totalProgress = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
        ];

        foreach ($courses as $course) {
            $this->info("Синхронизация курса: {$course->name} (ID: {$course->id})...");
            
            // Синхронизируем элементы курса (если еще не синхронизированы)
            $this->syncService->syncCourseActivities($course->id);
            
            // Синхронизируем прогресс студента
            $progressStats = $this->syncService->syncStudentProgress($course->id, $userId);
            
            $totalProgress['total'] += $progressStats['total'];
            $totalProgress['created'] += $progressStats['created'];
            $totalProgress['updated'] += $progressStats['updated'];
            $totalProgress['errors'] += $progressStats['errors'];
        }

        $this->displayProgressStats($totalProgress);
        $this->info("Синхронизация студента завершена!");
        return Command::SUCCESS;
    }

    /**
     * Полная синхронизация всех курсов и студентов
     *
     * @return int
     */
    protected function syncAll(): int
    {
        $this->info("Начало полной синхронизации элементов курса и прогресса студентов...");

        $stats = $this->syncService->syncAll();

        $this->info("\n=== Статистика синхронизации элементов курса ===");
        $this->displayActivityStats($stats['activities']);

        $this->info("\n=== Статистика синхронизации прогресса студентов ===");
        $this->displayProgressStats($stats['progress']);

        $this->info("\nПолная синхронизация завершена!");
        return Command::SUCCESS;
    }

    /**
     * Отобразить статистику синхронизации элементов курса
     *
     * @param array $stats
     * @return void
     */
    protected function displayActivityStats(array $stats): void
    {
        $this->line("Всего элементов: {$stats['total']}");
        $this->line("Создано: {$stats['created']}");
        $this->line("Обновлено: {$stats['updated']}");
        $this->line("Ошибок: {$stats['errors']}");

        if (!empty($stats['errors_list'])) {
            $this->warn("Список ошибок:");
            foreach ($stats['errors_list'] as $error) {
                $this->error("  - {$error['activity_type']} (ID: {$error['moodle_id']}): {$error['error']}");
            }
        }
    }

    /**
     * Отобразить статистику синхронизации прогресса студентов
     *
     * @param array $stats
     * @return void
     */
    protected function displayProgressStats(array $stats): void
    {
        $this->line("Всего записей прогресса: {$stats['total']}");
        $this->line("Создано: {$stats['created']}");
        $this->line("Обновлено: {$stats['updated']}");
        $this->line("Ошибок: {$stats['errors']}");

        if (!empty($stats['errors_list'])) {
            $this->warn("Список ошибок:");
            foreach ($stats['errors_list'] as $error) {
                $this->error("  - {$error['activity_type']}: {$error['error']}");
            }
        }
    }
}

