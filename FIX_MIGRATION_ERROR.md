# Исправление ошибки "Table already exists" при миграциях

## Проблема

При выполнении `php artisan migrate` возникает ошибка:
```
SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'users' already exists
```

Это происходит потому, что таблицы уже существуют в базе данных, но Laravel не знает об этом (миграции не отмечены как выполненные в таблице `migrations`).

## Решение

### Вариант 1: Использовать готовый скрипт (рекомендуется)

1. Обновите код с GitHub:
```bash
cd /var/www/www-root/data/www/m.dekan.pro
git pull origin main
```

2. Запустите скрипт для пометки существующих миграций:
```bash
php scripts/migrate/mark-existing-migrations-as-run.php
```

Скрипт автоматически:
- Проверит, какие таблицы существуют
- Помечает соответствующие миграции как выполненные
- Добавит их в таблицу `migrations`

3. Проверьте статус миграций:
```bash
php artisan migrate:status
```

4. Запустите миграции для новых таблиц (если есть):
```bash
php artisan migrate --force
```

### Вариант 2: Ручная пометка миграций через tinker

Если скрипт не работает, можно пометить миграции вручную:

1. Запустите Laravel Tinker:
```bash
php artisan tinker
```

2. Выполните следующие команды в tinker:
```php
// Получаем максимальный batch
$maxBatch = DB::table('migrations')->max('batch') ?? 0;
$newBatch = $maxBatch + 1;

// Добавляем базовые миграции
DB::table('migrations')->insert(['migration' => '0001_01_01_000000_create_users_table', 'batch' => $newBatch]);
DB::table('migrations')->insert(['migration' => '0001_01_01_000001_create_cache_table', 'batch' => $newBatch]);
DB::table('migrations')->insert(['migration' => '0001_01_01_000002_create_jobs_table', 'batch' => $newBatch]);
DB::table('migrations')->insert(['migration' => '2024_01_01_000001_create_roles_and_permissions_tables', 'batch' => $newBatch]);
DB::table('migrations')->insert(['migration' => '2024_01_01_000002_create_institutions_table', 'batch' => $newBatch]);
DB::table('migrations')->insert(['migration' => '2024_01_01_000003_create_programs_table', 'batch' => $newBatch]);
DB::table('migrations')->insert(['migration' => '2024_01_01_000004_create_courses_table', 'batch' => $newBatch]);

// И так далее для всех существующих миграций...
// (проверьте список миграций командой: ls database/migrations/)

exit
```

3. Проверьте статус:
```bash
php artisan migrate:status
```

4. Запустите миграции:
```bash
php artisan migrate --force
```

### Вариант 3: Использовать --pretend для проверки

Если хотите увидеть, какие миграции будут выполнены без фактического выполнения:

```bash
php artisan migrate --pretend
```

Это покажет SQL-запросы, которые будут выполнены, не выполняя их.

## Проверка после исправления

После пометки миграций проверьте:

1. Статус миграций:
```bash
php artisan migrate:status
```

Все существующие миграции должны быть помечены как "Ran".

2. Запустите миграции для новых таблиц:
```bash
php artisan migrate --force
```

Если все таблицы уже существуют, команда должна завершиться без ошибок.

3. Проверьте структуру базы данных:
```bash
php artisan db:show
```

## Дополнительная информация

- Если таблица `migrations` не существует, скрипт создаст её автоматически
- Скрипт проверяет существование таблиц перед пометкой миграций
- Миграции помечаются одним batch номером для удобства
