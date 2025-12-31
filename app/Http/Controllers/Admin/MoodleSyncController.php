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
            
            $stats = $this->syncService->syncAll();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Полная синхронизация завершена',
                    'stats' => $stats
                ]);
            }
            
            $message = "Полная синхронизация завершена. ";
            $message .= "Курсы: создано {$stats['courses']['created']}, обновлено {$stats['courses']['updated']}. ";
            $message .= "Записи студентов: создано {$stats['enrollments']['created']}, обновлено {$stats['enrollments']['updated']}.";
            
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
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $lines = file($logFile);
        $recentLogs = [];
        
        // Берем последние 50 строк и фильтруем по ключевым словам
        $relevantLines = array_slice($lines, -50);
        
        foreach ($relevantLines as $line) {
            if (stripos($line, 'Moodle') !== false || 
                stripos($line, 'синхронизац') !== false ||
                stripos($line, 'sync') !== false) {
                $recentLogs[] = trim($line);
            }
        }
        
        return array_slice($recentLogs, -10); // Возвращаем последние 10 релевантных строк
    }
}

