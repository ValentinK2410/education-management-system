# Исправление ошибки 404 для SSO маршрута

## Проблема
При переходе по ссылке `/sso/login?token=...` получаем ошибку 404 Not Found.

## Решение

### Шаг 1: Очистка кеша маршрутов

Выполните на сервере в директории Laravel приложения:

```bash
cd /path/to/education-management-system
php artisan route:clear
php artisan route:cache
```

Или если используете без кеширования:

```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### Шаг 2: Проверка маршрутов

Проверьте, что маршрут зарегистрирован:

```bash
php artisan route:list | grep sso
```

Должна быть строка:
```
GET|HEAD  sso/login ................ sso.login › Auth\SsoController@login
```

### Шаг 3: Проверка конфигурации веб-сервера

#### Для Nginx:

Убедитесь, что в конфигурации Nginx есть:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

#### Для Apache:

Убедитесь, что `.htaccess` файл существует и содержит:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### Шаг 4: Проверка файла маршрутов

Убедитесь, что в `routes/web.php` есть:

```php
// SSO маршруты (без CSRF защиты)
Route::get('/sso/login', [\App\Http\Controllers\Auth\SsoController::class, 'login'])
    ->name('sso.login');
```

### Шаг 5: Проверка контроллера

Убедитесь, что файл существует:
```
app/Http/Controllers/Auth/SsoController.php
```

### Шаг 6: Проверка исключения из CSRF

В `bootstrap/app.php` должно быть:

```php
$middleware->validateCsrfTokens(except: [
    'api/users/sync-from-wordpress',
    'sso/login',
]);
```

## Быстрая проверка

Выполните все команды очистки кеша:

```bash
php artisan optimize:clear
```

Эта команда очистит все кеши:
- config
- route
- view
- cache

## Альтернативное решение

Если проблема сохраняется, попробуйте добавить маршрут напрямую в `routes/web.php` в самом начале файла (перед другими маршрутами):

```php
use App\Http\Controllers\Auth\SsoController;

// SSO маршрут должен быть первым
Route::get('/sso/login', [SsoController::class, 'login'])->name('sso.login');
```

## Проверка после исправления

После выполнения всех шагов, попробуйте снова:

1. Войдите в WordPress
2. Выполните в консоли: `goToLaravel()`
3. Должен произойти автоматический переход и вход в Laravel

## Логи для отладки

Если проблема сохраняется, проверьте логи:

```bash
tail -f storage/logs/laravel.log
```

При переходе по SSO ссылке должны появиться записи в логе.

