<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup 
                            {--keep=30 : Количество дней хранения резервных копий}
                            {--path= : Путь для сохранения резервной копии}
                            {--table= : Имя таблицы для резервного копирования (если не указано, создается полная копия)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создание резервной копии базы данных';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Начало создания резервной копии базы данных...');

        try {
            $connection = DB::getDefaultConnection();
            $driver = config("database.connections.{$connection}.driver");

            $this->info("📊 Тип БД: {$driver}");
            $this->info("🔗 Соединение: {$connection}");

            $backupPath = $this->option('path') ?: $this->getDefaultBackupPath();
            $tableName = $this->option('table');
            
            // Проверяем существование таблицы, если указана
            if ($tableName) {
                if (!\Illuminate\Support\Facades\Schema::hasTable($tableName)) {
                    $this->error("❌ Таблица '{$tableName}' не существует");
                    return 1;
                }
                $this->info("📋 Резервное копирование таблицы: {$tableName}");
            } else {
                $this->info("📋 Создание полной резервной копии БД...");
            }

            $filename = $this->generateFilename($driver, $tableName);

            // Создаем директорию для резервных копий, если её нет
            if (!Storage::exists($backupPath)) {
                Storage::makeDirectory($backupPath);
            }

            $fullPath = storage_path("app/{$backupPath}/{$filename}");

            if ($tableName) {
                // Резервное копирование отдельной таблицы
                switch ($driver) {
                    case 'sqlite':
                        $this->backupSqliteTable($fullPath, $tableName);
                        break;
                    case 'mysql':
                        $this->backupMysqlTable($fullPath, $connection, $tableName);
                        break;
                    case 'pgsql':
                        $this->backupPostgresqlTable($fullPath, $connection, $tableName);
                        break;
                    default:
                        $this->error("❌ Неподдерживаемый тип БД: {$driver}");
                        return 1;
                }
            } else {
                // Полное резервное копирование
                switch ($driver) {
                    case 'sqlite':
                        $this->backupSqlite($fullPath);
                        break;
                    case 'mysql':
                        $this->backupMysql($fullPath, $connection);
                        break;
                    case 'pgsql':
                        $this->backupPostgresql($fullPath, $connection);
                        break;
                    default:
                        $this->error("❌ Неподдерживаемый тип БД: {$driver}");
                        return 1;
                }
            }

            if (file_exists($fullPath) && filesize($fullPath) > 0) {
                $fileSize = $this->formatBytes(filesize($fullPath));
                $this->info("✅ Резервная копия успешно создана!");
                $this->info("📁 Путь: {$fullPath}");
                $this->info("📦 Размер: {$fileSize}");

                // Очистка старых резервных копий
                $this->cleanOldBackups($backupPath);

                Log::info('Резервная копия БД создана', [
                    'path' => $fullPath,
                    'size' => filesize($fullPath),
                    'driver' => $driver
                ]);

                return 0;
            } else {
                $this->error("❌ Ошибка: файл резервной копии не создан или пуст");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("❌ Ошибка при создании резервной копии: " . $e->getMessage());
            Log::error('Ошибка создания резервной копии БД', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Создание резервной копии SQLite
     */
    protected function backupSqlite(string $backupPath): void
    {
        $databasePath = config("database.connections.sqlite.database");
        
        if (!file_exists($databasePath)) {
            throw new \Exception("Файл базы данных SQLite не найден: {$databasePath}");
        }

        $this->info("📋 Копирование файла БД...");
        
        if (!copy($databasePath, $backupPath)) {
            throw new \Exception("Не удалось скопировать файл БД");
        }

        // Блокируем БД для безопасного копирования
        DB::statement('PRAGMA wal_checkpoint(TRUNCATE)');
    }

    /**
     * Создание резервной копии MySQL
     */
    protected function backupMysql(string $backupPath, string $connection): void
    {
        $config = config("database.connections.{$connection}");
        $host = $config['host'];
        $port = $config['port'] ?? 3306;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $this->info("📋 Экспорт данных MySQL...");

        // Проверяем наличие mysqldump
        $mysqldumpPath = $this->findCommand('mysqldump');
        
        if (!$mysqldumpPath) {
            throw new \Exception("Команда mysqldump не найдена. Установите MySQL client tools.");
        }

        $command = sprintf(
            '%s --host=%s --port=%s --user=%s --password=%s %s > %s 2>&1',
            escapeshellarg($mysqldumpPath),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($backupPath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("Ошибка выполнения mysqldump: " . implode("\n", $output));
        }
    }

    /**
     * Создание резервной копии PostgreSQL
     */
    protected function backupPostgresql(string $backupPath, string $connection): void
    {
        $config = config("database.connections.{$connection}");
        $host = $config['host'];
        $port = $config['port'] ?? 5432;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $this->info("📋 Экспорт данных PostgreSQL...");

        // Проверяем наличие pg_dump
        $pgDumpPath = $this->findCommand('pg_dump');
        
        if (!$pgDumpPath) {
            throw new \Exception("Команда pg_dump не найдена. Установите PostgreSQL client tools.");
        }

        // Устанавливаем переменную окружения для пароля
        putenv("PGPASSWORD={$password}");

        $command = sprintf(
            '%s --host=%s --port=%s --username=%s --dbname=%s --file=%s --no-password 2>&1',
            escapeshellarg($pgDumpPath),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($backupPath)
        );

        exec($command, $output, $returnCode);

        // Удаляем переменную окружения
        putenv("PGPASSWORD");

        if ($returnCode !== 0) {
            throw new \Exception("Ошибка выполнения pg_dump: " . implode("\n", $output));
        }
    }

    /**
     * Генерация имени файла резервной копии
     */
    protected function generateFilename(string $driver, ?string $tableName = null): string
    {
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $extension = $driver === 'sqlite' ? 'sqlite' : 'sql';
        
        if ($tableName) {
            return "backup_table_{$tableName}_{$timestamp}.{$extension}";
        }
        
        return "backup_full_{$timestamp}.{$extension}";
    }

    /**
     * Получение пути по умолчанию для резервных копий
     */
    protected function getDefaultBackupPath(): string
    {
        return 'backups/database';
    }

    /**
     * Очистка старых резервных копий
     */
    protected function cleanOldBackups(string $backupPath): void
    {
        $keepDays = (int) $this->option('keep');
        $cutoffDate = Carbon::now()->subDays($keepDays);

        $this->info("🧹 Очистка резервных копий старше {$keepDays} дней...");

        $files = Storage::files($backupPath);
        $deletedCount = 0;

        foreach ($files as $file) {
            $filePath = storage_path("app/{$file}");
            if (file_exists($filePath)) {
                $fileTime = Carbon::createFromTimestamp(filemtime($filePath));
                if ($fileTime->lt($cutoffDate)) {
                    Storage::delete($file);
                    $deletedCount++;
                }
            }
        }

        if ($deletedCount > 0) {
            $this->info("✅ Удалено старых резервных копий: {$deletedCount}");
        } else {
            $this->info("ℹ️  Старых резервных копий не найдено");
        }
    }

    /**
     * Поиск команды в системе
     */
    protected function findCommand(string $command): ?string
    {
        $paths = [
            "/usr/bin/{$command}",
            "/usr/local/bin/{$command}",
            "/opt/homebrew/bin/{$command}",
            trim(shell_exec("which {$command} 2>/dev/null") ?: '')
        ];

        foreach ($paths as $path) {
            if ($path && file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Создание резервной копии таблицы SQLite
     */
    protected function backupSqliteTable(string $backupPath, string $tableName): void
    {
        $databasePath = config("database.connections.sqlite.database");
        
        if (!file_exists($databasePath)) {
            throw new \Exception("Файл базы данных SQLite не найден: {$databasePath}");
        }

        $this->info("📋 Экспорт таблицы {$tableName}...");

        // Экспортируем данные таблицы в SQL
        $data = DB::table($tableName)->get()->toArray();
        $sql = "-- Backup table {$tableName}\n";
        $sql .= "BEGIN TRANSACTION;\n";
        $sql .= "DELETE FROM {$tableName};\n";

        foreach ($data as $row) {
            $values = array_map(function ($value) {
                return $value === null ? 'NULL' : "'" . addslashes($value) . "'";
            }, (array)$row);
            $sql .= "INSERT INTO {$tableName} VALUES (" . implode(', ', $values) . ");\n";
        }

        $sql .= "COMMIT;\n";

        file_put_contents($backupPath, $sql);
    }

    /**
     * Создание резервной копии таблицы MySQL
     */
    protected function backupMysqlTable(string $backupPath, string $connection, string $tableName): void
    {
        $config = config("database.connections.{$connection}");
        $host = $config['host'];
        $port = $config['port'] ?? 3306;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $this->info("📋 Экспорт таблицы {$tableName}...");

        $mysqldumpPath = $this->findCommand('mysqldump');
        
        if (!$mysqldumpPath) {
            throw new \Exception("Команда mysqldump не найдена. Установите MySQL client tools.");
        }

        $command = sprintf(
            '%s --host=%s --port=%s --user=%s --password=%s %s %s > %s 2>&1',
            escapeshellarg($mysqldumpPath),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($tableName),
            escapeshellarg($backupPath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("Ошибка выполнения mysqldump: " . implode("\n", $output));
        }
    }

    /**
     * Создание резервной копии таблицы PostgreSQL
     */
    protected function backupPostgresqlTable(string $backupPath, string $connection, string $tableName): void
    {
        $config = config("database.connections.{$connection}");
        $host = $config['host'];
        $port = $config['port'] ?? 5432;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $this->info("📋 Экспорт таблицы {$tableName}...");

        $pgDumpPath = $this->findCommand('pg_dump');
        
        if (!$pgDumpPath) {
            throw new \Exception("Команда pg_dump не найдена. Установите PostgreSQL client tools.");
        }

        putenv("PGPASSWORD={$password}");

        $command = sprintf(
            '%s --host=%s --port=%s --username=%s --dbname=%s --table=%s --file=%s --no-password 2>&1',
            escapeshellarg($pgDumpPath),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($tableName),
            escapeshellarg($backupPath)
        );

        exec($command, $output, $returnCode);

        putenv("PGPASSWORD");

        if ($returnCode !== 0) {
            throw new \Exception("Ошибка выполнения pg_dump: " . implode("\n", $output));
        }
    }

    /**
     * Форматирование размера файла
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

