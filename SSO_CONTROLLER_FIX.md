# Исправление ошибки "Target class [SsoController] does not exist"

## Проблема
Laravel не может найти класс `SsoController`, хотя файл существует.

## Решение

### Шаг 1: Проверка существования файла

Убедитесь, что файл существует:
```
app/Http/Controllers/Auth/SsoController.php
```

### Шаг 2: Обновление автозагрузки Composer

Выполните на сервере:

```bash
cd /path/to/education-management-system
composer dump-autoload
```

Это обновит автозагрузку классов Composer.

### Шаг 3: Очистка всех кешей

```bash
php artisan optimize:clear
composer dump-autoload
```

### Шаг 4: Проверка namespace

Убедитесь, что в файле `app/Http/Controllers/Auth/SsoController.php` правильный namespace:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
// ... остальной код
```

### Шаг 5: Проверка регистрации маршрута

В `routes/web.php` должно быть:

```php
use App\Http\Controllers\Auth\SsoController;

Route::get('/sso/login', [SsoController::class, 'login'])
    ->name('sso.login');
```

### Шаг 6: Если файл отсутствует на сервере

Если файл не был загружен на сервер:

1. Убедитесь, что файл закоммичен:
```bash
git status
git add app/Http/Controllers/Auth/SsoController.php
git commit -m "Add SsoController"
git push
```

2. На сервере выполните:
```bash
git pull
composer dump-autoload
php artisan optimize:clear
```

### Шаг 7: Проверка прав доступа

Убедитесь, что файл имеет правильные права:

```bash
chmod 644 app/Http/Controllers/Auth/SsoController.php
```

### Шаг 8: Проверка синтаксиса PHP

Проверьте, что файл не содержит синтаксических ошибок:

```bash
php -l app/Http/Controllers/Auth/SsoController.php
```

## Быстрое решение

Выполните все команды последовательно:

```bash
cd /path/to/education-management-system
git pull
composer dump-autoload
php artisan optimize:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

## Проверка после исправления

После выполнения всех шагов проверьте:

```bash
php artisan route:list | grep sso
```

Должна быть строка с маршрутом `sso/login`.

Также можно проверить, что класс загружается:

```bash
php artisan tinker
```

Затем в tinker:
```php
class_exists('App\Http\Controllers\Auth\SsoController');
```

Должно вернуть `true`.

