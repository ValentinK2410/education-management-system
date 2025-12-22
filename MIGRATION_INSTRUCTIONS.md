# Инструкция по выполнению миграции

## Проблема
Ошибка при создании пользователя в Laravel:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'moodle_user_id' in 'field list'
```

## Решение
Нужно выполнить миграцию, которая добавляет поле `moodle_user_id` в таблицу `users`.

## Шаги выполнения

### 1. Подключитесь к серверу Laravel

```bash
ssh user@your-server
cd /path/to/education-management-system
```

### 2. Обновите код из Git

```bash
git pull origin main
```

### 3. Выполните миграцию

```bash
php artisan migrate
```

Если миграция уже была выполнена ранее, но поле отсутствует, можно выполнить:

```bash
php artisan migrate:refresh --path=/database/migrations/2024_12_22_000001_add_moodle_user_id_to_users_table.php
```

Или выполнить миграцию вручную через SQL:

```sql
ALTER TABLE `users` ADD COLUMN `moodle_user_id` BIGINT UNSIGNED NULL AFTER `id`;
```

### 4. Проверьте, что миграция выполнена

```bash
php artisan migrate:status
```

Должна быть запись:
```
2024_12_22_000001_add_moodle_user_id_to_users_table  Ran
```

### 5. Проверьте структуру таблицы

```bash
php artisan tinker
>>> Schema::hasColumn('users', 'moodle_user_id')
```

Должно вернуть `true`.

Или через SQL:

```sql
DESCRIBE users;
```

Должна быть колонка `moodle_user_id`.

## Альтернативный способ (если миграция не найдена)

Если файл миграции отсутствует, создайте его вручную:

```bash
php artisan make:migration add_moodle_user_id_to_users_table
```

Затем откройте созданный файл и добавьте код:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('moodle_user_id')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('moodle_user_id');
        });
    }
};
```

Затем выполните:

```bash
php artisan migrate
```

## После выполнения миграции

После успешного выполнения миграции попробуйте снова зарегистрировать пользователя на WordPress. Пользователь должен успешно создаться в Laravel.

