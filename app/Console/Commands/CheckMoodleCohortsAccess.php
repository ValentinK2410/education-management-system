<?php

namespace App\Console\Commands;

use App\Services\MoodleApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Команда для проверки доступа к cohorts в Moodle
 */
class CheckMoodleCohortsAccess extends Command
{
    /**
     * Название и сигнатура консольной команды
     *
     * @var string
     */
    protected $signature = 'moodle:check-cohorts-access';

    /**
     * Описание консольной команды
     *
     * @var string
     */
    protected $description = 'Проверить доступ к функции core_cohort_get_cohorts в Moodle API';

    /**
     * Выполнить консольную команду
     *
     * @return int
     */
    public function handle()
    {
        $this->info('=== Проверка доступа к cohorts в Moodle ===');
        $this->newLine();

        try {
            $moodleApi = new MoodleApiService();
        } catch (\InvalidArgumentException $e) {
            $this->error('Ошибка конфигурации Moodle: ' . $e->getMessage());
            $this->info('Проверьте настройки MOODLE_URL и MOODLE_TOKEN в .env файле');
            return Command::FAILURE;
        }

        // Проверка 1: Базовое подключение
        $this->info('1. Проверка базового подключения к Moodle API...');
        $testResult = $moodleApi->call('core_course_get_courses', []);
        
        if ($testResult === false) {
            $this->error('   ❌ Не удалось подключиться к Moodle API');
            $this->line('   Проверьте MOODLE_URL и MOODLE_TOKEN в .env файле');
            return Command::FAILURE;
        } elseif (isset($testResult['exception'])) {
            $this->error('   ❌ Ошибка подключения:');
            $this->line('      Тип: ' . ($testResult['exception'] ?? 'unknown'));
            $this->line('      Сообщение: ' . ($testResult['message'] ?? 'неизвестная ошибка'));
            return Command::FAILURE;
        } else {
            $this->info('   ✅ Подключение к Moodle API работает');
        }
        $this->newLine();

        // Проверка 2: Доступ к core_cohort_get_cohorts
        $this->info('2. Проверка доступа к функции core_cohort_get_cohorts...');
        $cohortResult = $moodleApi->call('core_cohort_get_cohorts', []);
        
        if ($cohortResult === false) {
            $this->error('   ❌ Запрос вернул false (проблема с подключением)');
            return Command::FAILURE;
        } elseif (isset($cohortResult['exception'])) {
            $exceptionType = $cohortResult['exception'] ?? 'unknown';
            $errorMessage = $cohortResult['message'] ?? 'неизвестная ошибка';
            $errorCode = $cohortResult['errorcode'] ?? 'N/A';
            
            $this->error('   ❌ Доступ к core_cohort_get_cohorts ОТСУТСТВУЕТ');
            $this->line('      Тип ошибки: ' . $exceptionType);
            $this->line('      Сообщение: ' . $errorMessage);
            $this->line('      Код ошибки: ' . $errorCode);
            $this->newLine();
            
            if ($exceptionType === 'webservice_access_exception') {
                $this->warn('   ⚠️  ПРОБЛЕМА: Токен не имеет прав на функцию core_cohort_get_cohorts');
                $this->newLine();
                $this->info('   📋 ИНСТРУКЦИЯ ПО ИСПРАВЛЕНИЮ:');
                $this->newLine();
                $this->line('   Шаг 1: Войдите в Moodle как администратор');
                $this->line('   Шаг 2: Перейдите: Настройки сайта → Плагины → Веб-сервисы → Внешние службы');
                $this->line('   Шаг 3: Найдите службу, которую использует ваш токен');
                $this->line('          (Проверьте токен в разделе "Управление токенами")');
                $this->line('   Шаг 4: Откройте службу для редактирования');
                $this->line('   Шаг 5: В разделе "Функции" найдите и добавьте:');
                $this->line('          - core_cohort_get_cohorts');
                $this->line('   Шаг 6: Сохраните изменения');
                $this->newLine();
                $this->line('   Альтернативный способ (если служба не редактируется):');
                $this->line('   Шаг 1: Настройки сайта → Плагины → Веб-сервисы → Управление протоколами');
                $this->line('   Шаг 2: Выберите протокол "REST" → "Изменить"');
                $this->line('   Шаг 3: В разделе "Функции" добавьте core_cohort_get_cohorts');
                $this->line('   Шаг 4: Сохраните изменения');
                $this->newLine();
                $this->info('   После добавления функции запустите эту команду снова для проверки.');
                $this->newLine();
                $this->line('   Подробная документация: MOODLE_COHORTS_SETUP.md');
            } else {
                $this->warn('   ⚠️  Неожиданный тип ошибки. Проверьте логи для деталей.');
            }
            
            return Command::FAILURE;
        } else {
            // Успешный доступ
            $cohorts = $moodleApi->getCohorts();
            if (is_array($cohorts)) {
                $this->info('   ✅ Доступ к core_cohort_get_cohorts РАБОТАЕТ');
                $this->line('      Найдено cohorts: ' . count($cohorts));
                
                if (count($cohorts) > 0) {
                    $this->newLine();
                    $this->info('   Примеры cohorts:');
                    foreach (array_slice($cohorts, 0, 5) as $cohort) {
                        $this->line('      - ID: ' . ($cohort['id'] ?? 'N/A') . ', Название: ' . ($cohort['name'] ?? 'N/A'));
                    }
                    if (count($cohorts) > 5) {
                        $this->line('      ... и еще ' . (count($cohorts) - 5) . ' cohorts');
                    }
                } else {
                    $this->warn('   ⚠️  Cohorts не найдены в Moodle (возможно, их просто нет)');
                }
                
                $this->newLine();
                $this->info('   ✅ Все готово! Можно запускать синхронизацию:');
                $this->line('      php artisan moodle:sync-cohorts');
                
                return Command::SUCCESS;
            } else {
                $this->warn('   ⚠️  Доступ есть, но формат ответа неожиданный');
                return Command::FAILURE;
            }
        }
    }
}
