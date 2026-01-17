# Инструкция по увеличению лимита памяти PHP

## Проблема
Ошибка: `Allowed memory size of 134217728 bytes exhausted` (128MB)

## Решения

### 1. Через .htaccess (уже добавлено)
В файле `public/.htaccess` уже добавлены строки:
```apache
php_value memory_limit 512M
php_value max_execution_time 300
```

**Важно:** Если используется Nginx или PHP-FPM, директивы `php_value` в .htaccess не работают. Используйте другие методы ниже.

### 2. Через public/index.php (уже добавлено)
В файле `public/index.php` уже добавлены строки:
```php
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '300');
```

### 3. Через php.ini (рекомендуется для серверов)

#### Для ISPmanager:
1. Войдите в панель ISPmanager
2. Перейдите в раздел **Настройки → PHP**
3. Найдите параметр `memory_limit`
4. Измените значение на `512M` или `1024M`
5. Сохраните изменения
6. Перезапустите PHP-FPM:
   ```bash
   systemctl restart php84-php-fpm
   # или
   systemctl restart php8.3-fpm
   ```

#### Для прямого редактирования php.ini:
1. Найдите файл php.ini:
   ```bash
   php --ini
   ```
2. Откройте файл php.ini (обычно `/etc/php/8.3/fpm/php.ini` или `/etc/php/8.4/fpm/php.ini`)
3. Найдите строку:
   ```ini
   memory_limit = 128M
   ```
4. Измените на:
   ```ini
   memory_limit = 512M
   ```
5. Также увеличьте время выполнения:
   ```ini
   max_execution_time = 300
   ```
6. Сохраните файл
7. Перезапустите PHP-FPM:
   ```bash
   systemctl restart php84-php-fpm
   # или
   systemctl restart php8.3-fpm
   ```

### 4. Через конфигурацию PHP-FPM pool

Если используется PHP-FPM, можно настроить лимит для конкретного пула:

1. Найдите конфигурационный файл пула (обычно в `/etc/php/8.3/fpm/pool.d/` или `/etc/php/8.4/fpm/pool.d/`)
2. Добавьте или измените:
   ```ini
   php_admin_value[memory_limit] = 512M
   php_admin_value[max_execution_time] = 300
   ```
3. Перезапустите PHP-FPM:
   ```bash
   systemctl restart php84-php-fpm
   ```

### 5. Проверка текущего лимита памяти

Создайте файл `public/phpinfo.php`:
```php
<?php
phpinfo();
```

Откройте в браузере: `https://dean.russianseminary.org/phpinfo.php`
Найдите строку `memory_limit` и проверьте значение.

**Важно:** После проверки удалите файл `phpinfo.php` из соображений безопасности!

### 6. Альтернативное решение через Laravel

Если нет доступа к серверу, можно увеличить память только для конкретного контроллера:

В файле `app/Http/Controllers/Admin/InstructorStatsController.php` в начале метода `show()`:
```php
public function show(User $instructor, Request $request)
{
    // Увеличение памяти для этого конкретного запроса
    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', '300');
    
    // ... остальной код
}
```

## Рекомендуемые значения

- **memory_limit**: `512M` (для больших объемов данных) или `1024M` (если проблема сохраняется)
- **max_execution_time**: `300` секунд (5 минут)

## После изменений

1. Очистите кеш Laravel:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

2. Перезапустите PHP-FPM (если изменяли php.ini или pool конфигурацию)

3. Проверьте страницу: `https://dean.russianseminary.org/admin/instructor-stats/1`

## Примечание

Если проблема все еще сохраняется после увеличения памяти до 512M, это может указывать на:
- Проблему с оптимизацией кода (уже исправлено)
- Необходимость дальнейшей оптимизации запросов к БД
- Возможность реализации пагинации или ленивой загрузки данных
