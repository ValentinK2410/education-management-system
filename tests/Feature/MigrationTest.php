<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест: проверка применения всех миграций
     */
    public function test_all_migrations_can_run(): void
    {
        Artisan::call('migrate:fresh');
        
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('courses'));
        $this->assertTrue(Schema::hasTable('programs'));
        $this->assertTrue(Schema::hasTable('institutions'));
        $this->assertTrue(Schema::hasTable('payments'));
        $this->assertTrue(Schema::hasTable('certificates'));
        $this->assertTrue(Schema::hasTable('data_versions'));
        $this->assertTrue(Schema::hasTable('activity_logs'));
    }

    /**
     * Тест: проверка наличия поля deleted_at в критических таблицах
     */
    public function test_soft_deletes_columns_exist(): void
    {
        Artisan::call('migrate:fresh');
        
        $tables = ['users', 'courses', 'programs', 'institutions', 'payments', 'certificates', 'enrollment_history'];
        
        foreach ($tables as $table) {
            $this->assertTrue(
                Schema::hasColumn($table, 'deleted_at'),
                "Таблица {$table} должна иметь поле deleted_at"
            );
        }
    }

    /**
     * Тест: проверка отката миграций
     */
    public function test_migrations_can_rollback(): void
    {
        Artisan::call('migrate:fresh');
        
        // Проверяем, что таблицы существуют
        $this->assertTrue(Schema::hasTable('data_versions'));
        $this->assertTrue(Schema::hasTable('activity_logs'));
        
        // Откатываем последние миграции
        Artisan::call('migrate:rollback', ['--step' => 2]);
        
        // Проверяем, что таблицы удалены
        $this->assertFalse(Schema::hasTable('data_versions'));
        $this->assertFalse(Schema::hasTable('activity_logs'));
    }

    /**
     * Тест: проверка целостности данных после миграций
     */
    public function test_data_integrity_after_migrations(): void
    {
        Artisan::call('migrate:fresh');
        Artisan::call('db:seed');
        
        // Проверяем, что нет нарушений внешних ключей
        $result = Artisan::call('db:check-integrity');
        
        // Код возврата 0 означает отсутствие ошибок
        $this->assertEquals(0, $result);
    }

    /**
     * Тест: проверка индексов
     */
    public function test_indexes_exist(): void
    {
        Artisan::call('migrate:fresh');
        
        // Проверяем индексы в таблице data_versions
        $indexes = DB::select("PRAGMA index_list('data_versions')");
        $indexNames = array_column($indexes, 'name');
        
        $this->assertContains('data_versions_versionable_type_versionable_id_index', $indexNames);
    }
}

