<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\MoodleSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Контроллер для синхронизации данных из Moodle через админ-панель
 */
class MoodleSyncController extends Controller
{
    /**
     * Сервис синхронизации Moodle
     *
     * @var MoodleSyncService
     */
    protected MoodleSyncService $syncService;

    /**
     * Конструктор
     */
    public function __construct()
    {
        try {
            $this->syncService = new MoodleSyncService();
        } catch (\InvalidArgumentException $e) {
            // Ошибка конфигурации будет обработана в методах синхронизации
            Log::error('Ошибка инициализации MoodleSyncService в контроллере', [
                'error' => $e->getMessage()
            ]);
            $this->syncService = null;
        }
    }

    /**
     * Показать страницу синхронизации
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Получаем статистику курсов
        $coursesCount = Course::whereNotNull('moodle_course_id')->count();
        $totalCourses = Course::count();
        
        // Получаем последние логи синхронизации (из файла логов)
        $recentLogs = $this->getRecentSyncLogs();

        return view('admin.moodle-sync.index', compact('coursesCount', 'totalCourses', 'recentLogs'));
    }

    /**
     * Синхронизировать курсы
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function syncCourses(Request $request)
    {
        try {
            // Увеличиваем время выполнения для длительной синхронизации
            set_time_limit(1800); // 30 минут
            ini_set('max_execution_time', '1800');
            ini_set('memory_limit', '512M');
            ignore_user_abort(true);
            
            if (!headers_sent()) {
                header('X-Accel-Buffering: no');
            }
            
            if (!$this->syncService) {
                throw new \Exception('Сервис синхронизации не инициализирован. Проверьте конфигурацию Moodle в .env файле.');
            }
            
            $stats = $this->syncService->syncCourses();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Синхронизация курсов завершена',
                    'stats' => $stats
                ]);
            }
            
            return redirect()->route('admin.moodle-sync.index')
                ->with('success', "Синхронизация курсов завершена. Создано: {$stats['created']}, Обновлено: {$stats['updated']}, Ошибок: {$stats['errors']}");
                
        } catch (\Exception $e) {
            Log::error('Ошибка синхронизации курсов через админ-панель', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка синхронизации: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('admin.moodle-sync.index')
                ->with('error', 'Ошибка синхронизации: ' . $e->getMessage());
        }
    }

    /**
     * Синхронизировать записи студентов для курса
     *
     * @param Request $request
     * @param int $courseId
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function syncCourseEnrollments(Request $request, int $courseId)
    {
        try {
            // Увеличиваем время выполнения для длительной синхронизации
            set_time_limit(1800); // 30 минут
            ini_set('max_execution_time', '1800');
            ini_set('memory_limit', '512M');
            ignore_user_abort(true);
            
            if (!headers_sent()) {
                header('X-Accel-Buffering: no');
            }
            
            if (!$this->syncService) {
                throw new \Exception('Сервис синхронизации не инициализирован. Проверьте конфигурацию Moodle в .env файле.');
            }
            
            $stats = $this->syncService->syncCourseEnrollments($courseId);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Синхронизация записей студентов завершена',
                    'stats' => $stats
                ]);
            }
            
            return redirect()->route('admin.moodle-sync.index')
                ->with('success', "Синхронизация записей студентов завершена. Создано: {$stats['created']}, Обновлено: {$stats['updated']}, Ошибок: {$stats['errors']}");
                
        } catch (\Exception $e) {
            Log::error('Ошибка синхронизации записей студентов через админ-панель', [
                'course_id' => $courseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка синхронизации: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('admin.moodle-sync.index')
                ->with('error', 'Ошибка синхронизации: ' . $e->getMessage());
        }
    }

    /**
     * Полная синхронизация (курсы + записи студентов)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function syncAll(Request $request)
    {
        try {
            // Увеличиваем время выполнения для длительной синхронизации
            set_time_limit(1800); // 30 минут
            ini_set('max_execution_time', '1800');
            ini_set('memory_limit', '512M'); // Увеличиваем лимит памяти
            
            // Отключаем ограничение времени выполнения для этого скрипта
            ignore_user_abort(true);
            
            // Устанавливаем заголовки для увеличения таймаута nginx
            if (!headers_sent()) {
                header('X-Accel-Buffering: no'); // Отключаем буферизацию nginx
            }
            
            if (!$this->syncService) {
                throw new \Exception('Сервис синхронизации не инициализирован. Проверьте конфигурацию Moodle в .env файле.');
            }
            
            $stats = $this->syncService->syncAll();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Полная синхронизация завершена',
                    'stats' => $stats
                ]);
            }
            
            $message = "Полная синхронизация завершена. ";
            $message .= "Курсы: создано {$stats['courses']['created']}, обновлено {$stats['courses']['updated']}, всего обработано: {$stats['courses']['total']}. ";
            $message .= "Записи студентов: создано {$stats['enrollments']['created']}, обновлено {$stats['enrollments']['updated']}, всего обработано: {$stats['enrollments']['total']}.";
            
            // Если все значения равны 0, добавляем предупреждение
            if ($stats['courses']['created'] == 0 && $stats['courses']['updated'] == 0 && 
                $stats['enrollments']['created'] == 0 && $stats['enrollments']['updated'] == 0) {
                $errorsCount = ($stats['courses']['errors'] ?? 0) + ($stats['enrollments']['errors'] ?? 0);
                if ($errorsCount > 0) {
                    $message .= " ⚠️ Внимание: обнаружены ошибки ({$errorsCount}). Проверьте логи для деталей.";
                    // Добавляем информацию об ошибках из списка
                    if (!empty($stats['courses']['errors_list'])) {
                        $firstError = $stats['courses']['errors_list'][0];
                        $message .= " Первая ошибка: " . ($firstError['error'] ?? 'неизвестная ошибка');
                    }
                    return redirect()->route('admin.moodle-sync.index')
                        ->with('warning', $message);
                } else {
                    // Проверяем, были ли вообще курсы для обработки
                    if (($stats['courses']['total'] ?? 0) == 0) {
                        $message .= " ⚠️ В Moodle не найдено курсов для синхронизации. Возможные причины: в Moodle нет курсов (кроме системного с id=1), токен не имеет прав на получение курсов. Проверьте права токена в Moodle: Site administration → Plugins → Web services → Manage tokens → [ваш токен] → Capabilities.";
                        return redirect()->route('admin.moodle-sync.index')
                            ->with('warning', $message);
                    } else {
                        $message .= " Все данные уже синхронизированы (нет изменений для обновления).";
                        return redirect()->route('admin.moodle-sync.index')
                            ->with('success', $message);
                    }
                }
            }
            
            // Добавляем информацию об ошибках, если они были
            if (($stats['courses']['errors'] ?? 0) > 0 || ($stats['enrollments']['errors'] ?? 0) > 0) {
                $message .= " ⚠️ Обнаружены ошибки: Курсы - {$stats['courses']['errors']}, Записи студентов - {$stats['enrollments']['errors']}. Подробности в логах.";
                return redirect()->route('admin.moodle-sync.index')
                    ->with('warning', $message); // Используем warning для смешанных результатов
            }

            return redirect()->route('admin.moodle-sync.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            Log::error('Ошибка полной синхронизации через админ-панель', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка синхронизации: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('admin.moodle-sync.index')
                ->with('error', 'Ошибка синхронизации: ' . $e->getMessage());
        }
    }

    /**
     * Получить последние логи синхронизации
     *
     * @return array
     */
    protected function getRecentSyncLogs(): array
    {
        // Читаем последние строки из лог-файла
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile) || !is_readable($logFile)) {
            return [];
        }
        
        // Увеличиваем лимит памяти для чтения логов
        $memoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', '256M');
        
        try {
            // Используем более эффективный способ чтения последних строк файла
            $lines = $this->readLastLines($logFile, 100);
            $recentLogs = [];
            
            // Фильтруем по ключевым словам
            foreach ($lines as $line) {
                if (stripos($line, 'Moodle') !== false || 
                    stripos($line, 'синхронизац') !== false ||
                    stripos($line, 'sync') !== false) {
                    $recentLogs[] = trim($line);
                }
            }
            
            // Возвращаем последние 10 релевантных строк
            return array_slice($recentLogs, -10);
        } catch (\Exception $e) {
            Log::error('Ошибка чтения логов синхронизации', [
                'error' => $e->getMessage()
            ]);
            return [];
        } finally {
            // Восстанавливаем лимит памяти
            ini_set('memory_limit', $memoryLimit);
        }
    }
    
    /**
     * Читает последние N строк из файла без загрузки всего файла в память
     *
     * @param string $filename Путь к файлу
     * @param int $lines Количество строк для чтения
     * @return array Массив строк
     */
    protected function readLastLines(string $filename, int $lines = 50): array
    {
        $file = @fopen($filename, 'r');
        if (!$file) {
            return [];
        }
        
        // Перемещаемся в конец файла
        fseek($file, 0, SEEK_END);
        $fileSize = ftell($file);
        
        // Если файл маленький, читаем его полностью
        if ($fileSize < 1024 * 1024) { // Меньше 1 МБ
            fseek($file, 0);
            $content = fread($file, $fileSize);
            fclose($file);
            $allLines = explode("\n", $content);
            return array_slice($allLines, -$lines);
        }
        
        // Для больших файлов читаем с конца
        $buffer = '';
        $lineCount = 0;
        $chunkSize = 8192; // 8 КБ
        
        // Читаем файл с конца по частям
        for ($pos = $fileSize - $chunkSize; $pos >= 0; $pos -= $chunkSize) {
            if ($pos < 0) {
                $chunkSize += $pos;
                $pos = 0;
            }
            
            fseek($file, $pos);
            $chunk = fread($file, $chunkSize);
            $buffer = $chunk . $buffer;
            
            // Подсчитываем количество строк
            $lineCount = substr_count($buffer, "\n");
            
            if ($lineCount >= $lines) {
                break;
            }
        }
        
        fclose($file);
        
        // Разбиваем на строки и берем последние N
        $allLines = explode("\n", $buffer);
        return array_slice($allLines, -$lines);
    }
}

