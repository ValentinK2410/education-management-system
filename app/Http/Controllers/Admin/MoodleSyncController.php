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
            if (!$this->syncService) {
                throw new \Exception('Сервис синхронизации не инициализирован. Проверьте конфигурацию Moodle в .env файле.');
            }
            
            // Получаем список курсов из Moodle для пошаговой синхронизации
            $moodleCourses = $this->syncService->getMoodleCoursesList();
            
            if ($moodleCourses === false || empty($moodleCourses)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Не удалось получить курсы из Moodle или список пуст'
                    ], 400);
                }
                
                return redirect()->route('admin.moodle-sync.index')
                    ->with('error', 'Не удалось получить курсы из Moodle или список пуст');
            }
            
            // Формируем список курсов для синхронизации
            $coursesList = array_map(function($course) {
                return [
                    'moodle_id' => $course['id'] ?? null,
                    'name' => $course['fullname'] ?? 'Без названия',
                    'shortname' => $course['shortname'] ?? null,
                ];
            }, $moodleCourses);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'sync_type' => 'courses',
                    'total_steps' => count($coursesList),
                    'courses' => $coursesList,
                    'message' => 'Начинаем пошаговую синхронизацию курсов. Всего курсов: ' . count($coursesList)
                ]);
            }
            
            // Для обычных запросов возвращаем старую логику (полная синхронизация)
            set_time_limit(1800);
            ini_set('max_execution_time', '1800');
            ini_set('memory_limit', '512M');
            ignore_user_abort(true);
            
            if (!headers_sent()) {
                header('X-Accel-Buffering: no');
            }
            
            $stats = $this->syncService->syncCourses();
            
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
            if (!$this->syncService) {
                throw new \Exception('Сервис синхронизации не инициализирован. Проверьте конфигурацию Moodle в .env файле.');
            }
            
            // Получаем список курсов из Moodle для пошаговой синхронизации
            $moodleCourses = $this->syncService->getMoodleCoursesList();
            
            if ($moodleCourses === false || empty($moodleCourses)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Не удалось получить курсы из Moodle или список пуст'
                    ], 400);
                }
                
                return redirect()->route('admin.moodle-sync.index')
                    ->with('error', 'Не удалось получить курсы из Moodle или список пуст');
            }
            
            // Формируем список курсов для синхронизации
            $coursesList = array_map(function($course) {
                return [
                    'moodle_id' => $course['id'] ?? null,
                    'name' => $course['fullname'] ?? 'Без названия',
                    'shortname' => $course['shortname'] ?? null,
                ];
            }, $moodleCourses);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'sync_type' => 'all',
                    'total_steps' => count($coursesList),
                    'courses' => $coursesList,
                    'message' => 'Начинаем пошаговую полную синхронизацию. Всего курсов: ' . count($coursesList)
                ]);
            }
            
            // Для обычных запросов возвращаем старую логику (полная синхронизация)
            set_time_limit(1800);
            ini_set('max_execution_time', '1800');
            ini_set('memory_limit', '512M');
            ignore_user_abort(true);
            
            if (!headers_sent()) {
                header('X-Accel-Buffering: no');
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

    /**
     * Синхронизировать один курс (для пошаговой синхронизации)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncChunk(Request $request)
    {
        try {
            // Проверяем авторизацию
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Необходима авторизация'
                ], 401);
            }
            
            if (!$this->syncService) {
                return response()->json([
                    'success' => false,
                    'message' => 'Сервис синхронизации недоступен. Проверьте настройки Moodle.'
                ], 500);
            }
            
            // Получаем параметры
            $moodleCourseId = $request->input('moodle_course_id');
            $syncEnrollments = $request->input('sync_enrollments', false);
            $step = (int)$request->input('step', 1);
            $totalSteps = (int)$request->input('total_steps', 1);
            
            if (!$moodleCourseId) {
                return response()->json([
                    'success' => false,
                    'step' => $step,
                    'total_steps' => $totalSteps,
                    'message' => 'Не указан ID курса в Moodle'
                ], 400);
            }
            
            Log::info('Запрос синхронизации курса (chunk)', [
                'moodle_course_id' => $moodleCourseId,
                'sync_enrollments' => $syncEnrollments,
                'step' => $step,
                'total_steps' => $totalSteps
            ]);
            
            // Получаем данные курса из Moodle
            $moodleCourses = $this->syncService->getMoodleCoursesList();
            $moodleCourse = null;
            
            foreach ($moodleCourses as $course) {
                if (($course['id'] ?? null) == $moodleCourseId) {
                    $moodleCourse = $course;
                    break;
                }
            }
            
            if (!$moodleCourse) {
                return response()->json([
                    'success' => false,
                    'step' => $step,
                    'total_steps' => $totalSteps,
                    'message' => 'Курс не найден в Moodle'
                ], 404);
            }
            
            $currentItem = [
                'type' => 'course',
                'moodle_id' => $moodleCourseId,
                'name' => $moodleCourse['fullname'] ?? 'Без названия'
            ];
            
            $stats = [
                'course' => ['created' => 0, 'updated' => 0, 'errors' => 0],
                'enrollments' => ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0]
            ];
            
            try {
                // Синхронизируем курс
                $courseResult = $this->syncService->syncCourse($moodleCourse);
                
                if ($courseResult['created']) {
                    $stats['course']['created'] = 1;
                } elseif ($courseResult['updated']) {
                    $stats['course']['updated'] = 1;
                }
                
                $localCourseId = $courseResult['course']->id ?? null;
                
                // Если нужно синхронизировать записи студентов
                if ($syncEnrollments && $localCourseId) {
                    try {
                        $enrollmentStats = $this->syncService->syncCourseEnrollments($localCourseId);
                        $stats['enrollments'] = $enrollmentStats;
                    } catch (\Exception $enrollmentException) {
                        Log::warning('Ошибка синхронизации записей студентов для курса (chunk)', [
                            'course_id' => $localCourseId,
                            'moodle_course_id' => $moodleCourseId,
                            'error' => $enrollmentException->getMessage()
                        ]);
                        $stats['enrollments']['errors'] = 1;
                    }
                }
                
                $message = sprintf(
                    'Синхронизирован курс: %s. Курс: %s, Записи: создано %d, обновлено %d.',
                    $moodleCourse['fullname'] ?? 'Без названия',
                    ($courseResult['created'] ? 'создан' : ($courseResult['updated'] ? 'обновлен' : 'без изменений')),
                    $stats['enrollments']['created'],
                    $stats['enrollments']['updated']
                );
                
                return response()->json([
                    'success' => true,
                    'step' => $step,
                    'total_steps' => $totalSteps,
                    'current_item' => $currentItem,
                    'stats' => $stats,
                    'has_more' => $step < $totalSteps,
                    'message' => $message
                ]);
            } catch (\Exception $syncException) {
                Log::error('Ошибка при синхронизации курса (chunk)', [
                    'moodle_course_id' => $moodleCourseId,
                    'step' => $step,
                    'error' => $syncException->getMessage(),
                    'file' => $syncException->getFile(),
                    'line' => $syncException->getLine()
                ]);
                
                return response()->json([
                    'success' => false,
                    'step' => $step,
                    'total_steps' => $totalSteps,
                    'current_item' => $currentItem,
                    'has_more' => $step < $totalSteps,
                    'message' => 'Ошибка при синхронизации курса: ' . $syncException->getMessage(),
                    'stats' => $stats
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Ошибка синхронизации курса (chunk)', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'step' => $request->input('step', 1),
                'total_steps' => $request->input('total_steps', 1),
                'message' => 'Ошибка синхронизации: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }
}

