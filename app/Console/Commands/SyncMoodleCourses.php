<?php

namespace App\Console\Commands;

use App\Services\MoodleSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Команда для синхронизации курсов и записей студентов из Moodle
 */
class SyncMoodleCourses extends Command
{
    /**
     * Название и сигнатура консольной команды
     *
     * @var string
     */
    protected $signature = 'moodle:sync 
                            {--type=all : Тип синхронизации: courses, enrollments, all}
                            {--course-id= : ID курса для синхронизации записей (только для type=enrollments)}
                            {--force : Принудительное обновление существующих записей}';

    /**
     * Описание консольной команды
     *
     * @var string
     */
    protected $description = 'Синхронизировать курсы и записи студентов из Moodle';

    /**
     * Сервис синхронизации Moodle
     *
     * @var MoodleSyncService
     */
    protected MoodleSyncService $syncService;

    /**
     * Выполнить консольную команду
     *
     * @return int
     */
    public function handle()
    {
        $this->syncService = new MoodleSyncService();
        
        $type = $this->option('type');
        
        $this->info('Начало синхронизации из Moodle...');
        $this->newLine();

        try {
            switch ($type) {
                case 'courses':
                    $this->syncCourses();
                    break;
                    
                case 'enrollments':
                    $this->syncEnrollments();
                    break;
                    
                case 'all':
                default:
                    $this->syncAll();
                    break;
            }
            
            $this->newLine();
            $this->info('Синхронизация завершена успешно!');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Ошибка при синхронизации: ' . $e->getMessage());
            Log::error('Ошибка выполнения команды синхронизации Moodle', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }

    /**
     * Синхронизировать только курсы
     */
    protected function syncCourses()
    {
        $this->info('Синхронизация курсов...');
        
        $stats = $this->syncService->syncCourses();
        
        $this->displayStats('Курсы', $stats);
    }

    /**
     * Синхронизировать только записи студентов
     */
    protected function syncEnrollments()
    {
        $courseId = $this->option('course-id');
        
        if ($courseId) {
            $this->info("Синхронизация записей студентов для курса ID: {$courseId}...");
            
            $stats = $this->syncService->syncCourseEnrollments($courseId);
            
            $this->displayStats('Записи студентов', $stats);
        } else {
            $this->error('Для синхронизации записей необходимо указать --course-id');
            $this->info('Использование: php artisan moodle:sync --type=enrollments --course-id=1');
        }
    }

    /**
     * Полная синхронизация
     */
    protected function syncAll()
    {
        $this->info('Полная синхронизация (курсы + записи студентов)...');
        $this->newLine();
        
        $stats = $this->syncService->syncAll();
        
        $this->displayStats('Курсы', $stats['courses']);
        $this->newLine();
        $this->displayStats('Записи студентов', $stats['enrollments']);
    }

    /**
     * Отобразить статистику синхронизации
     *
     * @param string $title Заголовок
     * @param array $stats Статистика
     */
    protected function displayStats(string $title, array $stats)
    {
        $this->info("Статистика синхронизации {$title}:");
        
        $this->table(
            ['Метрика', 'Значение'],
            [
                ['Всего обработано', $stats['total'] ?? 0],
                ['Создано', $stats['created'] ?? 0],
                ['Обновлено', $stats['updated'] ?? 0],
                ['Пропущено', $stats['skipped'] ?? 0],
                ['Ошибок', $stats['errors'] ?? 0],
            ]
        );
        
        if (!empty($stats['errors_list']) && $stats['errors'] > 0) {
            $this->warn('Список ошибок:');
            foreach ($stats['errors_list'] as $error) {
                $this->line("  - {$error['error']}");
            }
        }
    }
}

