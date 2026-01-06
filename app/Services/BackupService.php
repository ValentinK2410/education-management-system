<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class BackupService
{
    protected string $backupPath = 'backups/database';

    /**
     * Создать полную резервную копию БД
     */
    public function createFullBackup(): array
    {
        try {
            $connection = DB::getDefaultConnection();
            $driver = config("database.connections.{$connection}.driver");

            $filename = $this->generateFilename('full', $driver);
            $fullPath = storage_path("app/{$this->backupPath}/{$filename}");

            // Гарантируем создание директории перед записью файла
            $this->ensureDirectoryExists(dirname($fullPath));

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
                    throw new Exception("Неподдерживаемый тип БД: {$driver}");
            }

            if (!file_exists($fullPath) || filesize($fullPath) === 0) {
                throw new Exception("Файл резервной копии не создан или пуст");
            }

            $fileSize = filesize($fullPath);

            Log::info('Полная резервная копия БД создана', [
                'filename' => $filename,
                'path' => $fullPath,
                'size' => $fileSize,
                'driver' => $driver
            ]);

            return [
                'success' => true,
                'filename' => $filename,
                'path' => $fullPath,
                'size' => $fileSize,
                'type' => 'full',
                'created_at' => Carbon::now()
            ];
        } catch (Exception $e) {
            Log::error('Ошибка создания полной резервной копии', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Создать резервную копию отдельной таблицы
     */
    public function createTableBackup(string $tableName): array
    {
        try {
            $connection = DB::getDefaultConnection();
            $driver = config("database.connections.{$connection}.driver");

            // Проверяем существование таблицы
            if (!Schema::hasTable($tableName)) {
                throw new Exception("Таблица {$tableName} не существует");
            }

            $filename = $this->generateFilename('table', $driver, $tableName);
            $fullPath = storage_path("app/{$this->backupPath}/{$filename}");

            // Гарантируем создание директории перед записью файла
            $this->ensureDirectoryExists(dirname($fullPath));

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
                    throw new Exception("Неподдерживаемый тип БД: {$driver}");
            }

            if (!file_exists($fullPath) || filesize($fullPath) === 0) {
                throw new Exception("Файл резервной копии не создан или пуст");
            }

            $fileSize = filesize($fullPath);

            Log::info('Резервная копия таблицы создана', [
                'filename' => $filename,
                'table' => $tableName,
                'path' => $fullPath,
                'size' => $fileSize,
                'driver' => $driver
            ]);

            return [
                'success' => true,
                'filename' => $filename,
                'path' => $fullPath,
                'size' => $fileSize,
                'type' => 'table',
                'table' => $tableName,
                'created_at' => Carbon::now()
            ];
        } catch (Exception $e) {
            Log::error('Ошибка создания резервной копии таблицы', [
                'table' => $tableName,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Получить список всех резервных копий
     */
    public function getBackupsList(): array
    {
        $backups = [];
        $files = Storage::files($this->backupPath);

        foreach ($files as $file) {
            $filePath = storage_path("app/{$file}");
            if (file_exists($filePath)) {
                $filename = basename($file);
                $backups[] = $this->getBackupInfo($filename);
            }
        }

        // Сортируем по дате создания (новые первые)
        usort($backups, function ($a, $b) {
            return $b['created_at']->timestamp <=> $a['created_at']->timestamp;
        });

        return $backups;
    }

    /**
     * Получить информацию о резервной копии
     */
    public function getBackupInfo(string $filename): array
    {
        $filePath = storage_path("app/{$this->backupPath}/{$filename}");

        if (!file_exists($filePath)) {
            throw new Exception("Резервная копия не найдена: {$filename}");
        }

        $fileSize = filesize($filePath);
        $createdAt = Carbon::createFromTimestamp(filemtime($filePath));

        // Определяем тип резервной копии
        $type = 'full';
        $table = null;

        if (strpos($filename, 'backup_table_') === 0) {
            $type = 'table';
            // Извлекаем имя таблицы из имени файла
            preg_match('/backup_table_([^_]+)_/', $filename, $matches);
            $table = $matches[1] ?? null;
        }

        return [
            'filename' => $filename,
            'path' => $filePath,
            'size' => $fileSize,
            'size_formatted' => $this->formatBytes($fileSize),
            'type' => $type,
            'table' => $table,
            'created_at' => $createdAt,
            'created_at_formatted' => $createdAt->format('d.m.Y H:i:s')
        ];
    }

    /**
     * Восстановить из резервной копии
     */
    public function restoreFromBackup(string $filename, ?array $tables = null): bool
    {
        try {
            $backupInfo = $this->getBackupInfo($filename);
            $filePath = $backupInfo['path'];

            // Создаем резервную копию перед восстановлением
            $this->createFullBackup();

            $connection = DB::getDefaultConnection();
            $driver = config("database.connections.{$connection}.driver");

            if ($backupInfo['type'] === 'table' && $backupInfo['table']) {
                // Восстанавливаем только таблицу
                return $this->restoreTable($filename, $backupInfo['table']);
            }

            // Полное восстановление
            switch ($driver) {
                case 'sqlite':
                    return $this->restoreSqlite($filePath);
                case 'mysql':
                    return $this->restoreMysql($filePath, $connection, $tables);
                case 'pgsql':
                    return $this->restorePostgresql($filePath, $connection, $tables);
                default:
                    throw new Exception("Неподдерживаемый тип БД: {$driver}");
            }
        } catch (Exception $e) {
            Log::error('Ошибка восстановления из резервной копии', [
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Восстановить отдельную таблицу
     */
    public function restoreTable(string $filename, string $tableName): bool
    {
        try {
            $backupInfo = $this->getBackupInfo($filename);
            $filePath = $backupInfo['path'];

            // Создаем резервную копию таблицы перед восстановлением
            $this->createTableBackup($tableName);

            $connection = DB::getDefaultConnection();
            $driver = config("database.connections.{$connection}.driver");

            switch ($driver) {
                case 'sqlite':
                    return $this->restoreSqliteTable($filePath, $tableName);
                case 'mysql':
                    return $this->restoreMysqlTable($filePath, $connection, $tableName);
                case 'pgsql':
                    return $this->restorePostgresqlTable($filePath, $connection, $tableName);
                default:
                    throw new Exception("Неподдерживаемый тип БД: {$driver}");
            }
        } catch (Exception $e) {
            Log::error('Ошибка восстановления таблицы', [
                'filename' => $filename,
                'table' => $tableName,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Удалить резервную копию
     */
    public function deleteBackup(string $filename): bool
    {
        try {
            $filePath = storage_path("app/{$this->backupPath}/{$filename}");

            if (!file_exists($filePath)) {
                throw new Exception("Резервная копия не найдена: {$filename}");
            }

            $deleted = unlink($filePath);

            if ($deleted) {
                Log::info('Резервная копия удалена', ['filename' => $filename]);
            }

            return $deleted;
        } catch (Exception $e) {
            Log::error('Ошибка удаления резервной копии', [
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Получить список всех таблиц БД
     */
    public function getTablesList(): array
    {
        $connection = DB::getDefaultConnection();
        $driver = config("database.connections.{$connection}.driver");

        switch ($driver) {
            case 'sqlite':
                $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                return array_map(function ($table) {
                    return $table->name;
                }, $tables);
            case 'mysql':
                $database = config("database.connections.{$connection}.database");
                $tables = DB::select("SHOW TABLES");
                $key = "Tables_in_{$database}";
                return array_map(function ($table) use ($key) {
                    return $table->$key;
                }, $tables);
            case 'pgsql':
                $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
                return array_map(function ($table) {
                    return $table->tablename;
                }, $tables);
            default:
                return [];
        }
    }

    /**
     * Генерация имени файла резервной копии
     */
    protected function generateFilename(string $type, string $driver, ?string $tableName = null): string
    {
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $extension = $driver === 'sqlite' ? 'sqlite' : 'sql';

        if ($type === 'table' && $tableName) {
            return "backup_table_{$tableName}_{$timestamp}.{$extension}";
        }

        return "backup_full_{$timestamp}.{$extension}";
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

    /**
     * Гарантировать существование директории
     */
    protected function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                throw new Exception("Не удалось создать директорию: {$directory}");
            }
        }
    }

    // Методы резервного копирования для разных типов БД

    protected function backupSqlite(string $backupPath): void
    {
        $databasePath = config("database.connections.sqlite.database");

        if (!file_exists($databasePath)) {
            throw new Exception("Файл базы данных SQLite не найден: {$databasePath}");
        }

        // Гарантируем создание директории перед копированием файла
        $this->ensureDirectoryExists(dirname($backupPath));

        if (!copy($databasePath, $backupPath)) {
            throw new Exception("Не удалось скопировать файл БД");
        }

        DB::statement('PRAGMA wal_checkpoint(TRUNCATE)');
    }

    protected function backupSqliteTable(string $backupPath, string $tableName): void
    {
        $databasePath = config("database.connections.sqlite.database");

        if (!file_exists($databasePath)) {
            throw new Exception("Файл базы данных SQLite не найден: {$databasePath}");
        }

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

        // Гарантируем создание директории перед записью файла
        $this->ensureDirectoryExists(dirname($backupPath));

        file_put_contents($backupPath, $sql);
    }

    protected function backupMysql(string $backupPath, string $connection): void
    {
        $config = config("database.connections.{$connection}");
        $host = $config['host'];
        $port = $config['port'] ?? 3306;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        // Проверяем, не содержит ли хост порт (формат host:port)
        if (strpos($host, ':') !== false) {
            list($host, $portFromHost) = explode(':', $host, 2);
            if (is_numeric($portFromHost)) {
                $port = (int)$portFromHost;
            }
        }

        // Убеждаемся, что порт - число
        $port = (int)$port;

        $mysqldumpPath = $this->findCommand('mysqldump');

        if (!$mysqldumpPath) {
            throw new Exception("Команда mysqldump не найдена. Установите MySQL client tools.");
        }

        // Используем переменную окружения для пароля (более безопасно)
        $env = [];
        if ($password) {
            $env['MYSQL_PWD'] = $password;
        }

        // Формируем команду без пароля в командной строке
        $command = sprintf(
            '%s --host=%s --port=%d --user=%s --single-transaction --routines --triggers %s',
            escapeshellarg($mysqldumpPath),
            escapeshellarg($host),
            $port,
            escapeshellarg($username),
            escapeshellarg($database)
        );

        // Выполняем команду с перенаправлением вывода
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        $process = proc_open($command, $descriptorspec, $pipes, null, $env);

        if (!is_resource($process)) {
            throw new Exception("Не удалось запустить процесс mysqldump");
        }

        // Закрываем stdin
        fclose($pipes[0]);

        // Читаем stdout и stderr
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $returnCode = proc_close($process);

        // Гарантируем создание директории перед записью файла
        $this->ensureDirectoryExists(dirname($backupPath));

        // Записываем вывод в файл
        if ($output) {
            file_put_contents($backupPath, $output);
        }

        if ($returnCode !== 0) {
            $errorMessage = $errors ?: $output ?: "Неизвестная ошибка (код: {$returnCode})";
            throw new Exception("Ошибка выполнения mysqldump: " . trim($errorMessage));
        }

        // Проверяем, что файл создан и не пуст
        if (!file_exists($backupPath) || filesize($backupPath) === 0) {
            throw new Exception("Резервная копия не создана или пуста. Проверьте права доступа и наличие данных в БД.");
        }
    }

    protected function backupMysqlTable(string $backupPath, string $connection, string $tableName): void
    {
        $config = config("database.connections.{$connection}");
        $host = $config['host'];
        $port = $config['port'] ?? 3306;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        // Проверяем, не содержит ли хост порт (формат host:port)
        if (strpos($host, ':') !== false) {
            list($host, $portFromHost) = explode(':', $host, 2);
            if (is_numeric($portFromHost)) {
                $port = (int)$portFromHost;
            }
        }

        // Убеждаемся, что порт - число
        $port = (int)$port;

        $mysqldumpPath = $this->findCommand('mysqldump');

        if (!$mysqldumpPath) {
            throw new Exception("Команда mysqldump не найдена. Установите MySQL client tools.");
        }

        // Используем переменную окружения для пароля (более безопасно)
        $env = [];
        if ($password) {
            $env['MYSQL_PWD'] = $password;
        }

        // Формируем команду без пароля в командной строке
        $command = sprintf(
            '%s --host=%s --port=%d --user=%s --single-transaction --routines --triggers %s %s',
            escapeshellarg($mysqldumpPath),
            escapeshellarg($host),
            $port,
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($tableName)
        );

        // Выполняем команду с перенаправлением вывода
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        $process = proc_open($command, $descriptorspec, $pipes, null, $env);

        if (!is_resource($process)) {
            throw new Exception("Не удалось запустить процесс mysqldump");
        }

        // Закрываем stdin
        fclose($pipes[0]);

        // Читаем stdout и stderr
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $returnCode = proc_close($process);

        // Гарантируем создание директории перед записью файла
        $this->ensureDirectoryExists(dirname($backupPath));

        // Записываем вывод в файл
        if ($output) {
            file_put_contents($backupPath, $output);
        }

        if ($returnCode !== 0) {
            $errorMessage = $errors ?: $output ?: "Неизвестная ошибка (код: {$returnCode})";
            throw new Exception("Ошибка выполнения mysqldump для таблицы {$tableName}: " . trim($errorMessage));
        }

        // Проверяем, что файл создан и не пуст
        if (!file_exists($backupPath) || filesize($backupPath) === 0) {
            throw new Exception("Резервная копия таблицы {$tableName} не создана или пуста.");
        }
    }

    protected function backupPostgresql(string $backupPath, string $connection): void
    {
        $config = config("database.connections.{$connection}");
        $host = $config['host'];
        $port = $config['port'] ?? 5432;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $pgDumpPath = $this->findCommand('pg_dump');

        if (!$pgDumpPath) {
            throw new Exception("Команда pg_dump не найдена");
        }

        // Гарантируем создание директории перед записью файла
        $this->ensureDirectoryExists(dirname($backupPath));

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

        putenv("PGPASSWORD");

        if ($returnCode !== 0) {
            throw new Exception("Ошибка выполнения pg_dump: " . implode("\n", $output));
        }
    }

    protected function backupPostgresqlTable(string $backupPath, string $connection, string $tableName): void
    {
        $config = config("database.connections.{$connection}");
        $host = $config['host'];
        $port = $config['port'] ?? 5432;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $pgDumpPath = $this->findCommand('pg_dump');

        if (!$pgDumpPath) {
            throw new Exception("Команда pg_dump не найдена");
        }

        // Гарантируем создание директории перед записью файла
        $this->ensureDirectoryExists(dirname($backupPath));

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
            throw new Exception("Ошибка выполнения pg_dump: " . implode("\n", $output));
        }
    }

    // Методы восстановления

    protected function restoreSqlite(string $filePath): bool
    {
        $databasePath = config("database.connections.sqlite.database");

        if (!file_exists($filePath)) {
            throw new Exception("Файл резервной копии не найден: {$filePath}");
        }

        return copy($filePath, $databasePath);
    }

    protected function restoreSqliteTable(string $filePath, string $tableName): bool
    {
        if (!file_exists($filePath)) {
            throw new Exception("Файл резервной копии не найден: {$filePath}");
        }

        $sql = file_get_contents($filePath);
        DB::unprepared($sql);

        return true;
    }

    protected function restoreMysql(string $filePath, string $connection, ?array $tables = null): bool
    {
        $config = config("database.connections.{$connection}");
        $host = $config['host'];
        $port = $config['port'] ?? 3306;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        // Проверяем, не содержит ли хост порт (формат host:port)
        if (strpos($host, ':') !== false) {
            list($host, $portFromHost) = explode(':', $host, 2);
            if (is_numeric($portFromHost)) {
                $port = (int)$portFromHost;
            }
        }

        // Убеждаемся, что порт - число
        $port = (int)$port;

        $mysqlPath = $this->findCommand('mysql');

        if (!$mysqlPath) {
            throw new Exception("Команда mysql не найдена. Установите MySQL client tools.");
        }

        if (!file_exists($filePath)) {
            throw new Exception("Файл резервной копии не найден: {$filePath}");
        }

        // Используем переменную окружения для пароля
        $env = [];
        if ($password) {
            $env['MYSQL_PWD'] = $password;
        }

        // Читаем содержимое файла
        $sqlContent = file_get_contents($filePath);
        if ($sqlContent === false) {
            throw new Exception("Не удалось прочитать файл резервной копии");
        }

        // Формируем команду без пароля в командной строке
        $command = sprintf(
            '%s --host=%s --port=%d --user=%s %s',
            escapeshellarg($mysqlPath),
            escapeshellarg($host),
            $port,
            escapeshellarg($username),
            escapeshellarg($database)
        );

        // Выполняем команду с передачей SQL через stdin
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        $process = proc_open($command, $descriptorspec, $pipes, null, $env);

        if (!is_resource($process)) {
            throw new Exception("Не удалось запустить процесс mysql");
        }

        // Записываем SQL в stdin
        fwrite($pipes[0], $sqlContent);
        fclose($pipes[0]);

        // Читаем stderr для проверки ошибок
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $returnCode = proc_close($process);

        if ($returnCode !== 0) {
            $errorMessage = $errors ?: "Неизвестная ошибка (код: {$returnCode})";
            throw new Exception("Ошибка восстановления из резервной копии: " . trim($errorMessage));
        }

        return true;
    }

    protected function restoreMysqlTable(string $filePath, string $connection, string $tableName): bool
    {
        // Для восстановления таблицы используем тот же метод, что и для полного восстановления
        // MySQL автоматически обработает только команды для указанной таблицы из SQL файла
        return $this->restoreMysql($filePath, $connection, [$tableName]);
    }

    protected function restorePostgresql(string $filePath, string $connection, ?array $tables = null): bool
    {
        $config = config("database.connections.{$connection}");
        $host = $config['host'];
        $port = $config['port'] ?? 5432;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $psqlPath = $this->findCommand('psql');

        if (!$psqlPath) {
            throw new Exception("Команда psql не найдена");
        }

        putenv("PGPASSWORD={$password}");

        $command = sprintf(
            '%s --host=%s --port=%s --username=%s --dbname=%s --file=%s --no-password 2>&1',
            escapeshellarg($psqlPath),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($filePath)
        );

        exec($command, $output, $returnCode);

        putenv("PGPASSWORD");

        return $returnCode === 0;
    }

    protected function restorePostgresqlTable(string $filePath, string $connection, string $tableName): bool
    {
        return $this->restorePostgresql($filePath, $connection, [$tableName]);
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
}

