<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckDataIntegrity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:check-integrity 
                            {--fix : Попытаться исправить найденные проблемы}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверка целостности данных в базе данных';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Начало проверки целостности данных...');
        $this->newLine();

        $errors = [];
        $warnings = [];

        // Проверка внешних ключей
        $this->info('1. Проверка внешних ключей...');
        $foreignKeyIssues = $this->checkForeignKeys();
        if (!empty($foreignKeyIssues)) {
            $errors = array_merge($errors, $foreignKeyIssues);
        }

        // Проверка связанных записей
        $this->info('2. Проверка связанных записей...');
        $relationIssues = $this->checkRelations();
        if (!empty($relationIssues)) {
            $warnings = array_merge($warnings, $relationIssues);
        }

        // Проверка дубликатов
        $this->info('3. Проверка дубликатов...');
        $duplicateIssues = $this->checkDuplicates();
        if (!empty($duplicateIssues)) {
            $warnings = array_merge($warnings, $duplicateIssues);
        }

        // Проверка NULL значений в обязательных полях
        $this->info('4. Проверка обязательных полей...');
        $nullIssues = $this->checkRequiredFields();
        if (!empty($nullIssues)) {
            $errors = array_merge($errors, $nullIssues);
        }

        // Вывод результатов
        $this->newLine();
        $this->info('📊 Результаты проверки:');
        $this->newLine();

        if (empty($errors) && empty($warnings)) {
            $this->info('✅ Целостность данных в порядке!');
            return 0;
        }

        if (!empty($errors)) {
            $this->error('❌ Найдено ошибок: ' . count($errors));
            foreach ($errors as $error) {
                $this->line("   - {$error}");
            }
            $this->newLine();
        }

        if (!empty($warnings)) {
            $this->warn('⚠️  Найдено предупреждений: ' . count($warnings));
            foreach ($warnings as $warning) {
                $this->line("   - {$warning}");
            }
            $this->newLine();
        }

        // Попытка исправления
        if ($this->option('fix') && !empty($errors)) {
            $this->info('🔧 Попытка исправления ошибок...');
            // Здесь можно добавить логику автоматического исправления
            $this->warn('   Автоматическое исправление не реализовано. Исправьте ошибки вручную.');
        }

        return empty($errors) ? 0 : 1;
    }

    /**
     * Проверка внешних ключей
     */
    protected function checkForeignKeys(): array
    {
        $issues = [];
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // Для SQLite проверяем целостность через PRAGMA
            $result = DB::select("PRAGMA foreign_key_check");
            if (!empty($result)) {
                foreach ($result as $row) {
                    $issues[] = "Нарушение внешнего ключа в таблице {$row->table}: запись ID {$row->rowid}";
                }
            }
        } elseif ($driver === 'mysql') {
            // Для MySQL проверяем через INFORMATION_SCHEMA
            $result = DB::select("
                SELECT 
                    TABLE_NAME,
                    COLUMN_NAME,
                    CONSTRAINT_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            foreach ($result as $fk) {
                $checkQuery = "
                    SELECT COUNT(*) as count
                    FROM {$fk->TABLE_NAME} t1
                    LEFT JOIN {$fk->REFERENCED_TABLE_NAME} t2
                    ON t1.{$fk->COLUMN_NAME} = t2.{$fk->REFERENCED_COLUMN_NAME}
                    WHERE t1.{$fk->COLUMN_NAME} IS NOT NULL
                    AND t2.{$fk->REFERENCED_COLUMN_NAME} IS NULL
                ";

                $violations = DB::select($checkQuery);
                if ($violations[0]->count > 0) {
                    $issues[] = "Нарушение внешнего ключа {$fk->CONSTRAINT_NAME} в таблице {$fk->TABLE_NAME}: {$violations[0]->count} записей";
                }
            }
        }

        return $issues;
    }

    /**
     * Проверка связанных записей
     */
    protected function checkRelations(): array
    {
        $warnings = [];

        // Проверяем пользователей без ролей
        $usersWithoutRoles = DB::table('users')
            ->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->whereNull('user_roles.user_id')
            ->count();

        if ($usersWithoutRoles > 0) {
            $warnings[] = "Найдено {$usersWithoutRoles} пользователей без ролей";
        }

        // Проверяем курсы без программы (если program_id не nullable)
        if (Schema::hasColumn('courses', 'program_id')) {
            $coursesWithoutProgram = DB::table('courses')
                ->whereNull('program_id')
                ->count();

            if ($coursesWithoutProgram > 0) {
                $warnings[] = "Найдено {$coursesWithoutProgram} курсов без программы";
            }
        }

        return $warnings;
    }

    /**
     * Проверка дубликатов
     */
    protected function checkDuplicates(): array
    {
        $warnings = [];

        // Проверяем дубликаты email
        $duplicateEmails = DB::table('users')
            ->select('email', DB::raw('COUNT(*) as count'))
            ->groupBy('email')
            ->having('count', '>', 1)
            ->get();

        if ($duplicateEmails->isNotEmpty()) {
            $warnings[] = "Найдено дубликатов email: " . $duplicateEmails->count();
        }

        return $warnings;
    }

    /**
     * Проверка обязательных полей
     */
    protected function checkRequiredFields(): array
    {
        $errors = [];

        // Проверяем пользователей без email
        $usersWithoutEmail = DB::table('users')
            ->whereNull('email')
            ->orWhere('email', '')
            ->count();

        if ($usersWithoutEmail > 0) {
            $errors[] = "Найдено {$usersWithoutEmail} пользователей без email";
        }

        // Проверяем курсы без названия
        $coursesWithoutName = DB::table('courses')
            ->whereNull('name')
            ->orWhere('name', '')
            ->count();

        if ($coursesWithoutName > 0) {
            $errors[] = "Найдено {$coursesWithoutName} курсов без названия";
        }

        return $errors;
    }
}

