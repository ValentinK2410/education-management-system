<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MoodleApiService;

/**
 * Команда для проверки конфигурации Moodle
 */
class CheckMoodleConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moodle:check-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверка конфигурации Moodle API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Проверка конфигурации Moodle...');
        $this->newLine();

        // Проверяем переменные окружения
        $url = config('services.moodle.url', '');
        $token = config('services.moodle.token', '');
        $enabled = config('services.moodle.enabled', true);

        // Проверка URL
        $this->line('MOODLE_URL: ' . ($url ?: '<fg=red>НЕ НАСТРОЕН</>'));
        if ($url) {
            if (preg_match('/^https?:\/\//i', $url)) {
                $this->line('  ✓ URL содержит протокол');
            } else {
                $this->error('  ✗ URL должен содержать протокол (http:// или https://)');
                $this->line('  Пример правильного значения: https://class.dekan.pro');
            }
        }

        $this->newLine();

        // Проверка токена
        $this->line('MOODLE_TOKEN: ' . ($token ? '<fg=green>НАСТРОЕН</> (' . substr($token, 0, 10) . '...)' : '<fg=red>НЕ НАСТРОЕН</>'));
        if (!$token) {
            $this->newLine();
            $this->warn('Для получения токена Moodle:');
            $this->line('1. Войдите в Moodle как администратор');
            $this->line('2. Перейдите: Site administration → Plugins → Web services → Manage tokens');
            $this->line('3. Создайте новый токен для пользователя с правами администратора');
            $this->line('4. Скопируйте токен и добавьте в .env файл:');
            $this->line('   MOODLE_TOKEN=ваш_токен_здесь');
        }

        $this->newLine();

        // Проверка включенности синхронизации
        $this->line('MOODLE_SYNC_ENABLED: ' . ($enabled ? '<fg=green>ДА</>' : '<fg=yellow>НЕТ</>'));

        $this->newLine();

        // Если конфигурация полная, пробуем подключиться
        if ($url && $token && preg_match('/^https?:\/\//i', $url)) {
            $this->info('Попытка подключения к Moodle API...');
            
            try {
                $moodleApi = new MoodleApiService();
                
                // Пробуем получить список курсов для проверки подключения
                $this->line('Проверка подключения...');
                $courses = $moodleApi->getAllCourses();
                
                if ($courses !== false) {
                    $count = is_array($courses) ? count($courses) : 0;
                    $this->info("✓ Подключение успешно! Найдено курсов: {$count}");
                } else {
                    $this->error('✗ Не удалось получить данные из Moodle. Проверьте токен и права доступа.');
                }
            } catch (\InvalidArgumentException $e) {
                $this->error('✗ Ошибка конфигурации: ' . $e->getMessage());
            } catch (\Exception $e) {
                $this->error('✗ Ошибка подключения: ' . $e->getMessage());
                $this->line('Проверьте:');
                $this->line('  - Правильность URL Moodle');
                $this->line('  - Правильность токена');
                $this->line('  - Доступность Moodle сервера из сети');
            }
        } else {
            $this->warn('Конфигурация неполная. Заполните все необходимые параметры в .env файле.');
        }

        $this->newLine();
        $this->line('Для применения изменений в .env выполните:');
        $this->line('  php artisan config:clear');

        return 0;
    }
}

