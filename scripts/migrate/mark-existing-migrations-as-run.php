<?php

/**
 * Скрипт для пометки существующих миграций как выполненных
 * Использование: php scripts/migrate/mark-existing-migrations-as-run.php
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "🔍 Проверяем существующие таблицы и миграции...\n\n";

// Список миграций в порядке их выполнения
$migrations = [
    '0001_01_01_000000_create_users_table',
    '0001_01_01_000001_create_cache_table',
    '0001_01_01_000002_create_jobs_table',
    '2024_01_01_000001_create_roles_and_permissions_tables',
    '2024_01_01_000002_create_institutions_table',
    '2024_01_01_000003_create_programs_table',
    '2024_01_01_000004_create_courses_table',
    '2024_01_01_000005_add_user_preferences_to_users_table',
    '2024_01_01_000006_add_payment_fields_to_courses_table',
    '2024_01_01_000007_add_payment_fields_to_programs_table',
    '2024_01_01_000008_add_extended_profile_fields_to_users_table',
    '2024_01_01_000009_create_user_relations_tables',
    '2024_01_01_000010_fix_price_field_in_programs_table',
    '2024_01_01_000011_create_reviews_table',
    '2024_01_01_000012_create_events_table',
    '2024_01_01_000013_add_code_and_credits_to_programs_table',
    '2024_01_01_000014_add_location_to_programs_table',
    '2024_01_01_000015_add_image_to_courses_table',
    '2024_01_01_000016_create_certificate_templates_table',
    '2024_01_01_000017_create_certificates_table',
    '2024_01_01_000018_create_enrollment_history_table',
    '2024_01_01_000019_create_payments_table',
    '2024_01_01_000020_add_payment_status_to_user_relations',
    '2024_01_01_000020_create_course_activities_table',
    '2024_01_01_000021_create_student_activity_progress_table',
    '2024_01_01_000022_create_student_activity_history_table',
    '2024_01_01_000023_add_unique_index_to_course_activities_table',
    '2024_01_02_000001_add_order_to_courses_table',
    '2024_01_02_000002_create_settings_table',
    '2024_12_22_000001_add_moodle_user_id_to_users_table',
    '2024_12_22_000002_add_wordpress_and_moodle_fields_to_courses_table',
    '2024_12_23_000001_make_program_id_nullable_in_courses_table',
    '2025_01_01_000001_add_indexes_to_users_table_for_search',
    '2025_01_01_000002_add_deleted_at_to_users_table',
    '2025_01_01_000003_add_deleted_at_to_courses_table',
    '2025_01_01_000004_add_deleted_at_to_programs_table',
    '2025_01_01_000005_add_deleted_at_to_institutions_table',
    '2025_01_01_000006_add_deleted_at_to_payments_table',
    '2025_01_01_000007_add_deleted_at_to_certificates_table',
    '2025_01_01_000008_add_deleted_at_to_enrollment_history_table',
    '2025_01_01_000009_create_data_versions_table',
    '2025_01_01_000010_create_activity_logs_table',
];

// Проверяем, какие таблицы существуют
$existingTables = [];
$tablesToCheck = [
    'users',
    'cache',
    'jobs',
    'roles',
    'permissions',
    'role_permissions',
    'user_roles',
    'institutions',
    'programs',
    'courses',
    'user_programs',
    'user_courses',
    'user_institutions',
    'reviews',
    'events',
    'certificate_templates',
    'certificates',
    'enrollment_history',
    'payments',
    'course_activities',
    'student_activity_progress',
    'student_activity_history',
    'settings',
    'data_versions',
    'activity_logs',
];

echo "📋 Проверяем существующие таблицы:\n";
foreach ($tablesToCheck as $table) {
    if (Schema::hasTable($table)) {
        $existingTables[] = $table;
        echo "  ✅ Таблица '{$table}' существует\n";
    } else {
        echo "  ❌ Таблица '{$table}' не существует\n";
    }
}

echo "\n📝 Проверяем таблицу migrations:\n";
if (!Schema::hasTable('migrations')) {
    echo "  ❌ Таблица 'migrations' не существует. Создаём её...\n";
    DB::statement('CREATE TABLE IF NOT EXISTS migrations (
        id int(10) unsigned NOT NULL AUTO_INCREMENT,
        migration varchar(255) NOT NULL,
        batch int(11) NOT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    echo "  ✅ Таблица 'migrations' создана\n";
} else {
    echo "  ✅ Таблица 'migrations' существует\n";
}

// Получаем список уже выполненных миграций
$executedMigrations = DB::table('migrations')->pluck('migration')->toArray();
echo "\n📊 Уже выполненные миграции: " . count($executedMigrations) . "\n";

// Определяем batch для новых миграций
$maxBatch = DB::table('migrations')->max('batch') ?? 0;
$newBatch = $maxBatch + 1;

echo "\n🔄 Помечаем существующие миграции как выполненные...\n";
$added = 0;

// Создаем карту миграций к таблицам для более точной проверки
$migrationToTables = [
    '0001_01_01_000000_create_users_table' => ['users'],
    '0001_01_01_000001_create_cache_table' => ['cache'],
    '0001_01_01_000002_create_jobs_table' => ['jobs'],
    '2024_01_01_000001_create_roles_and_permissions_tables' => ['roles', 'permissions', 'role_permissions', 'user_roles'],
    '2024_01_01_000002_create_institutions_table' => ['institutions'],
    '2024_01_01_000003_create_programs_table' => ['programs'],
    '2024_01_01_000004_create_courses_table' => ['courses'],
    '2024_01_01_000009_create_user_relations_tables' => ['user_programs', 'user_courses', 'user_institutions'],
    '2024_01_01_000011_create_reviews_table' => ['reviews'],
    '2024_01_01_000012_create_events_table' => ['events'],
    '2024_01_01_000016_create_certificate_templates_table' => ['certificate_templates'],
    '2024_01_01_000017_create_certificates_table' => ['certificates'],
    '2024_01_01_000018_create_enrollment_history_table' => ['enrollment_history'],
    '2024_01_01_000019_create_payments_table' => ['payments'],
    '2024_01_01_000020_create_course_activities_table' => ['course_activities'],
    '2024_01_01_000021_create_student_activity_progress_table' => ['student_activity_progress'],
    '2024_01_01_000022_create_student_activity_history_table' => ['student_activity_history'],
    '2024_01_02_000002_create_settings_table' => ['settings'],
    '2025_01_01_000009_create_data_versions_table' => ['data_versions'],
    '2025_01_01_000010_create_activity_logs_table' => ['activity_logs'],
];

foreach ($migrations as $migration) {
    if (!in_array($migration, $executedMigrations)) {
        // Проверяем, соответствует ли миграция существующим таблицам
        $shouldAdd = false;
        
        // Сначала проверяем по карте миграций
        if (isset($migrationToTables[$migration])) {
            $requiredTables = $migrationToTables[$migration];
            $allTablesExist = true;
            foreach ($requiredTables as $table) {
                if (!Schema::hasTable($table)) {
                    $allTablesExist = false;
                    break;
                }
            }
            if ($allTablesExist) {
                $shouldAdd = true;
            } elseif (count($requiredTables) > 1) {
                // Если миграция создает несколько таблиц, но хотя бы одна существует
                // считаем миграцию выполненной (возможно частично)
                $someTablesExist = false;
                foreach ($requiredTables as $table) {
                    if (Schema::hasTable($table)) {
                        $someTablesExist = true;
                        break;
                    }
                }
                if ($someTablesExist) {
                    echo "  ⚠️  Внимание: миграция {$migration} выполнена частично (не все таблицы существуют)\n";
                    $shouldAdd = true;
                }
            }
        }
        
        // Если не нашли в карте, используем старую логику
        if (!$shouldAdd) {
        
        // Простая проверка: если таблица users существует, значит базовая миграция выполнена
        if ($migration === '0001_01_01_000000_create_users_table' && Schema::hasTable('users')) {
            $shouldAdd = true;
        }
        // Для других миграций проверяем соответствующие таблицы
        elseif (strpos($migration, 'create_users_table') !== false && Schema::hasTable('users')) {
            $shouldAdd = true;
        }
        elseif (strpos($migration, 'create_cache_table') !== false && Schema::hasTable('cache')) {
            $shouldAdd = true;
        }
        elseif (strpos($migration, 'create_jobs_table') !== false && Schema::hasTable('jobs')) {
            $shouldAdd = true;
        }
        elseif (strpos($migration, 'create_roles_and_permissions_tables') !== false && Schema::hasTable('roles') && Schema::hasTable('permissions')) {
            $shouldAdd = true;
        }
        elseif (strpos($migration, 'create_institutions_table') !== false && Schema::hasTable('institutions')) {
            $shouldAdd = true;
        }
        elseif (strpos($migration, 'create_programs_table') !== false && Schema::hasTable('programs')) {
            $shouldAdd = true;
        }
        elseif (strpos($migration, 'create_courses_table') !== false && Schema::hasTable('courses')) {
            $shouldAdd = true;
        }
        elseif (strpos($migration, 'create_user_relations_tables') !== false) {
            // Проверяем все три таблицы, создаваемые этой миграцией
            if (Schema::hasTable('user_programs') && 
                Schema::hasTable('user_courses') && 
                Schema::hasTable('user_institutions')) {
                $shouldAdd = true;
            } elseif (Schema::hasTable('user_programs') || 
                      Schema::hasTable('user_courses') || 
                      Schema::hasTable('user_institutions')) {
                // Если хотя бы одна таблица существует, считаем миграцию выполненной
                // (возможно, миграция была выполнена частично)
                echo "  ⚠️  Внимание: миграция {$migration} выполнена частично (не все таблицы существуют)\n";
                $shouldAdd = true;
            }
        }
        elseif (strpos($migration, 'create_reviews_table') !== false && Schema::hasTable('reviews')) {
            $shouldAdd = true;
        }
        elseif (strpos($migration, 'create_events_table') !== false && Schema::hasTable('events')) {
            $shouldAdd = true;
        }
        elseif (strpos($migration, 'create_certificate_templates_table') !== false && Schema::hasTable('certificate_templates')) {
            $shouldAdd = true;
        }
        elseif (strpos($migration, 'create_certificates_table') !== false && Schema::hasTable('certificates')) {
            $shouldAdd = true;
        }
        elseif (strpos($migration, 'create_enrollment_history_table') !== false && Schema::hasTable('enrollment_history')) {
            $shouldAdd = true;
        }
        elseif (strpos($migration, 'create_payments_table') !== false && Schema::hasTable('payments')) {
            $shouldAdd = true;
        }
        elseif (strpos($migration, 'create_course_activities_table') !== false && Schema::hasTable('course_activities')) {
            $shouldAdd = true;
        }
        elseif (strpos($migration, 'create_student_activity_progress_table') !== false && Schema::hasTable('student_activity_progress')) {
            $shouldAdd = true;
        }
        elseif (strpos($migration, 'create_student_activity_history_table') !== false && Schema::hasTable('student_activity_history')) {
            $shouldAdd = true;
        }
        elseif (strpos($migration, 'create_settings_table') !== false && Schema::hasTable('settings')) {
            $shouldAdd = true;
        }
        elseif (strpos($migration, 'create_data_versions_table') !== false && Schema::hasTable('data_versions')) {
            $shouldAdd = true;
        }
        elseif (strpos($migration, 'create_activity_logs_table') !== false && Schema::hasTable('activity_logs')) {
            $shouldAdd = true;
        }
        // Для миграций изменения таблиц (add_*, fix_*) всегда добавляем, если базовая таблица существует
        elseif (strpos($migration, 'add_') !== false || strpos($migration, 'fix_') !== false || strpos($migration, 'make_') !== false) {
            // Для миграций изменения таблиц, проверяем наличие базовой таблицы
            if (strpos($migration, 'users') !== false && Schema::hasTable('users')) {
                $shouldAdd = true;
            }
            elseif (strpos($migration, 'courses') !== false && Schema::hasTable('courses')) {
                $shouldAdd = true;
            }
            elseif (strpos($migration, 'programs') !== false && Schema::hasTable('programs')) {
                $shouldAdd = true;
            }
            elseif (strpos($migration, 'institutions') !== false && Schema::hasTable('institutions')) {
                $shouldAdd = true;
            }
            elseif (strpos($migration, 'payments') !== false && Schema::hasTable('payments')) {
                $shouldAdd = true;
            }
            elseif (strpos($migration, 'certificates') !== false && Schema::hasTable('certificates')) {
                $shouldAdd = true;
            }
            elseif (strpos($migration, 'enrollment_history') !== false && Schema::hasTable('enrollment_history')) {
                $shouldAdd = true;
            }
            elseif (strpos($migration, 'course_activities') !== false && Schema::hasTable('course_activities')) {
                $shouldAdd = true;
            }
        }
        
        if ($shouldAdd) {
            DB::table('migrations')->insert([
                'migration' => $migration,
                'batch' => $newBatch
            ]);
            echo "  ✅ Добавлена миграция: {$migration}\n";
            $added++;
        } else {
            echo "  ⏭️  Пропущена миграция: {$migration} (таблица не существует)\n";
        }
    } else {
        echo "  ✓ Миграция уже выполнена: {$migration}\n";
    }
}

echo "\n✅ Готово! Добавлено миграций: {$added}\n";
echo "\n📋 Теперь можно запустить: php artisan migrate:status\n";
echo "📋 И затем: php artisan migrate\n";
