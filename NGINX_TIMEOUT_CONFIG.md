# Настройка таймаутов Nginx для синхронизации Moodle

## Проблема
При синхронизации Moodle возникает ошибка **504 Gateway Timeout** из-за превышения времени ожидания ответа.

## Решение

### Вариант 1: Автоматический скрипт (рекомендуется)

Выполните на сервере:
```bash
cd /var/www/www-root/data/www/dean.russianseminary.org
bash fix-timeout-settings-ispmanager.sh
```

### Вариант 2: Ручная настройка

#### 1. Настройка Nginx FastCGI таймаутов

Создайте или отредактируйте файл:
```
/etc/nginx/vhosts-resources/dean.russianseminary.org/dynamic/php.conf
```

Добавьте или обновите следующие строки:
```nginx
fastcgi_read_timeout 1800;
fastcgi_send_timeout 1800;
```

#### 2. Настройка таймаутов в основном блоке server

Отредактируйте файл конфигурации сайта (обычно находится в панели управления или в):
```
/etc/nginx/vhosts-resources/dean.russianseminary.org/dean.russianseminary.org.conf
```

Добавьте в блок `server {` (после строки `server {`):
```nginx
client_body_timeout 1800;
send_timeout 1800;
```

Или добавьте вручную в конфигурацию сайта в панели управления:
- Найдите блок `location @php {`
- Добавьте перед закрывающей скобкой:
```nginx
fastcgi_read_timeout 1800;
fastcgi_send_timeout 1800;
```

#### 3. Настройка PHP-FPM

Найдите конфигурацию PHP-FPM (обычно `/var/www/php-fpm/5.conf` или `/etc/php/5.6/fpm/pool.d/www.conf`).

Добавьте или обновите:
```ini
request_terminate_timeout = 1800
```

#### 4. Настройка PHP.ini

Найдите файл `php.ini` для вашей версии PHP (обычно `/etc/php/5.6/fpm/php.ini`).

Обновите:
```ini
max_execution_time = 1800
```

#### 5. Перезапуск сервисов

```bash
# Проверка синтаксиса
nginx -t

# Перезагрузка Nginx
systemctl reload nginx
# или
service nginx reload

# Перезапуск PHP-FPM
systemctl restart php5.6-fpm
# или через панель управления
```

## Итоговая конфигурация

После применения настроек ваша конфигурация должна содержать:

**В `/etc/nginx/vhosts-resources/dean.russianseminary.org/dynamic/php.conf`:**
```nginx
fastcgi_read_timeout 1800;
fastcgi_send_timeout 1800;
```

**В блоке `server {` вашего сайта:**
```nginx
client_body_timeout 1800;
send_timeout 1800;
```

**В конфигурации PHP-FPM:**
```ini
request_terminate_timeout = 1800
```

**В php.ini:**
```ini
max_execution_time = 1800
```

## Проверка

После применения настроек попробуйте снова запустить синхронизацию:
```
https://dean.russianseminary.org/admin/moodle-sync/all
```

Если ошибка 504 все еще возникает, проверьте логи:
- `/var/www/httpd-logs/dean.russianseminary.org.error.log`
- `storage/logs/laravel.log`

## Примечания

- Все таймауты установлены на **1800 секунд (30 минут)**
- Если синхронизация занимает больше времени, увеличьте значения пропорционально
- Убедитесь, что у PHP-FPM достаточно памяти (`memory_limit = 512M` или больше)
