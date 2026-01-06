<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Отобразить список всех резервных копий
     */
    public function index()
    {
        try {
            $backups = $this->backupService->getBackupsList();
            $tables = $this->backupService->getTablesList();

            return view('admin.backups.index', compact('backups', 'tables'));
        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Ошибка при загрузке списка резервных копий: ' . $e->getMessage());
        }
    }

    /**
     * Показать форму создания резервной копии
     */
    public function create()
    {
        try {
            $tables = $this->backupService->getTablesList();
            return view('admin.backups.create', compact('tables'));
        } catch (\Exception $e) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'Ошибка при загрузке списка таблиц: ' . $e->getMessage());
        }
    }

    /**
     * Создать резервную копию
     */
    public function store(Request $request)
    {
        $tablesList = $this->backupService->getTablesList();
        
        $request->validate([
            'type' => 'required|in:full,table',
            'tables' => 'required_if:type,table|array',
            'tables.*' => 'required|string|in:' . implode(',', $tablesList)
        ]);

        try {
            if ($request->type === 'full') {
                $result = $this->backupService->createFullBackup();
                $message = 'Полная резервная копия успешно создана';
            } else {
                $tables = $request->input('tables', []);
                $results = [];
                foreach ($tables as $table) {
                    $results[] = $this->backupService->createTableBackup($table);
                }
                $result = ['success' => true, 'backups' => $results];
                $message = 'Резервные копии таблиц успешно созданы (' . count($tables) . ' таблиц)';
            }

            Log::info('Резервная копия создана через интерфейс', [
                'type' => $request->type,
                'user_id' => auth()->id()
            ]);

            return redirect()->route('admin.backups.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Ошибка создания резервной копии через интерфейс', [
                'error' => $e->getMessage(),
                'type' => $request->type,
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Ошибка при создании резервной копии: ' . $e->getMessage());
        }
    }

    /**
     * Показать информацию о резервной копии
     */
    public function show(string $filename)
    {
        try {
            $backup = $this->backupService->getBackupInfo($filename);
            return view('admin.backups.show', compact('backup'));
        } catch (\Exception $e) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'Резервная копия не найдена: ' . $e->getMessage());
        }
    }

    /**
     * Восстановить из резервной копии
     */
    public function restore(Request $request, string $filename)
    {
        $request->validate([
            'confirm' => 'required|accepted',
            'tables' => 'nullable|array'
        ]);

        try {
            // Создаем резервную копию перед восстановлением
            $this->backupService->createFullBackup();

            $tables = $request->input('tables');
            $this->backupService->restoreFromBackup($filename, $tables);

            Log::info('Восстановление из резервной копии выполнено', [
                'filename' => $filename,
                'user_id' => auth()->id()
            ]);

            return redirect()->route('admin.backups.index')
                ->with('success', 'Восстановление из резервной копии выполнено успешно');
        } catch (\Exception $e) {
            Log::error('Ошибка восстановления из резервной копии', [
                'filename' => $filename,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->with('error', 'Ошибка при восстановлении: ' . $e->getMessage());
        }
    }

    /**
     * Восстановить отдельную таблицу
     */
    public function restoreTable(Request $request, string $filename, string $table)
    {
        $request->validate([
            'confirm' => 'required|accepted'
        ]);

        try {
            // Создаем резервную копию таблицы перед восстановлением
            $this->backupService->createTableBackup($table);

            $this->backupService->restoreTable($filename, $table);

            Log::info('Восстановление таблицы выполнено', [
                'filename' => $filename,
                'table' => $table,
                'user_id' => auth()->id()
            ]);

            return redirect()->route('admin.backups.index')
                ->with('success', "Таблица {$table} успешно восстановлена");
        } catch (\Exception $e) {
            Log::error('Ошибка восстановления таблицы', [
                'filename' => $filename,
                'table' => $table,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->with('error', 'Ошибка при восстановлении таблицы: ' . $e->getMessage());
        }
    }

    /**
     * Скачать резервную копию
     */
    public function download(string $filename)
    {
        try {
            $backup = $this->backupService->getBackupInfo($filename);
            $filePath = $backup['path'];

            if (!file_exists($filePath)) {
                abort(404, 'Файл не найден');
            }

            Log::info('Резервная копия скачана', [
                'filename' => $filename,
                'user_id' => auth()->id()
            ]);

            return response()->download($filePath, $filename);
        } catch (\Exception $e) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'Ошибка при скачивании: ' . $e->getMessage());
        }
    }

    /**
     * Удалить резервную копию
     */
    public function destroy(Request $request, string $filename)
    {
        $request->validate([
            'confirm' => 'required|accepted'
        ]);

        try {
            $this->backupService->deleteBackup($filename);

            Log::info('Резервная копия удалена', [
                'filename' => $filename,
                'user_id' => auth()->id()
            ]);

            return redirect()->route('admin.backups.index')
                ->with('success', 'Резервная копия успешно удалена');
        } catch (\Exception $e) {
            Log::error('Ошибка удаления резервной копии', [
                'filename' => $filename,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->with('error', 'Ошибка при удалении: ' . $e->getMessage());
        }
    }
}

