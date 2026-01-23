<?php

namespace App\Console\Commands;

use App\Services\MoodleCohortSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Команда для синхронизации глобальных групп (cohorts) из Moodle
 */
class SyncMoodleCohorts extends Command
{
    /**
     * Название и сигнатура консольной команды
     *
     * @var string
     */
    protected $signature = 'moodle:sync-cohorts';

    /**
     * Описание консольной команды
     *
     * @var string
     */
    protected $description = 'Синхронизировать глобальные группы (cohorts) из Moodle';

    /**
     * Сервис синхронизации cohorts из Moodle
     *
     * @var MoodleCohortSyncService
     */
    protected MoodleCohortSyncService $syncService;

    /**
     * Выполнить консольную команду
     *
     * @return int
     */
    public function handle()
    {
        try {
            $this->syncService = new MoodleCohortSyncService();
        } catch (\InvalidArgumentException $e) {
            $this->error('Ошибка конфигурации Moodle: ' . $e->getMessage());
            $this->info('Проверьте настройки MOODLE_URL и MOODLE_TOKEN в .env файле');
            return Command::FAILURE;
        }
        
        $this->info('Начало синхронизации глобальных групп (cohorts) из Moodle...');
        $this->newLine();

        try {
            $stats = $this->syncService->syncCohorts();
            
            $this->displayStats($stats);
            
            $this->newLine();
            $this->info('Синхронизация завершена успешно!');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Ошибка при синхронизации: ' . $e->getMessage());
            Log::error('Ошибка выполнения команды синхронизации cohorts из Moodle', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }

    /**
     * Отобразить статистику синхронизации
     *
     * @param array $stats Статистика
     */
    protected function displayStats(array $stats)
    {
        $this->info('Статистика синхронизации глобальных групп:');
        
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
                $cohortInfo = '';
                if (isset($error['cohort_name'])) {
                    $cohortInfo = " ({$error['cohort_name']})";
                }
                $this->line("  - {$error['error']}{$cohortInfo}");
                
                // Если ошибка связана с правами доступа, показываем подсказку
                if (isset($error['type']) && $error['type'] === 'api_error') {
                    if (isset($error['hint'])) {
                        $this->line("    Подсказка: {$error['hint']}");
                    } else {
                        $this->line("    Подсказка: Проверьте права доступа токена на функцию core_cohort_get_cohorts");
                        $this->line("    См. документацию: MOODLE_COHORTS_SETUP.md");
                    }
                }
            }
        }
    }
}
