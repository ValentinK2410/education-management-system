# Инструкции по резервному копированию и восстановлению

## Автоматическое резервное копирование

Система автоматически создает резервные копии базы данных:

- **Ежедневно** в 2:00 ночи
- **Еженедельно** (воскресенье) в 3:00 ночи (полная копия с хранением 90 дней)

Резервные копии сохраняются в `storage/app/backups/database/`

## Ручное резервное копирование

### Через Artisan команду

```bash
php artisan db:backup
```

**Опции:**
- `--keep=30` - количество дней хранения резервных копий (по умолчанию 30)
- `--path=backups/custom` - путь для сохранения резервной копии

**Пример:**
```bash
php artisan db:backup --keep=60 --path=backups/manual
```

### Через скрипт

```bash
./backup-database.sh
```

## Формат резервных копий

Резервные копии сохраняются с именами:
- SQLite: `backup_sqlite_YYYY-MM-DD_HHMMSS.sqlite`
- MySQL/PostgreSQL: `backup_mysql_YYYY-MM-DD_HHMMSS.sql` или `backup_pgsql_YYYY-MM-DD_HHMMSS.sql`

## Восстановление из резервной копии

### SQLite

```bash
# Остановите приложение (если возможно)
# Скопируйте резервную копию
cp storage/app/backups/database/backup_sqlite_2025-01-01_020000.sqlite database/database.sqlite

# Установите права доступа
chmod 664 database/database.sqlite
chown www-data:www-data database/database.sqlite
```

### MySQL

```bash
# Восстановление из дампа
mysql -u username -p database_name < storage/app/backups/database/backup_mysql_2025-01-01_020000.sql

# Или через команду
mysql -u username -p database_name < путь/к/файлу.sql
```

### PostgreSQL

```bash
# Восстановление из дампа
psql -U username -d database_name -f storage/app/backups/database/backup_pgsql_2025-01-01_020000.sql

# Или через команду
psql -U username -d database_name < путь/к/файлу.sql
```

## Восстановление перед применением миграций

### Рекомендуемый процесс:

1. **Создайте резервную копию:**
   ```bash
   php artisan db:backup
   ```

2. **Примените миграции:**
   ```bash
   php artisan migrate
   ```

3. **Проверьте целостность:**
   ```bash
   php artisan db:check-integrity
   ```

4. **Если что-то пошло не так, восстановите:**
   ```bash
   # Откатите миграции
   php artisan migrate:rollback --step=1
   
   # Восстановите из резервной копии (см. выше)
   ```

## Управление резервными копиями

### Просмотр списка резервных копий

```bash
ls -lh storage/app/backups/database/
```

### Очистка старых резервных копий

Старые резервные копии автоматически удаляются при создании новых (согласно параметру `--keep`).

Для ручной очистки:
```bash
# Удалить резервные копии старше 30 дней
find storage/app/backups/database/ -name "backup_*" -mtime +30 -delete
```

### Настройка хранения

По умолчанию резервные копии хранятся:
- Ежедневные: 30 дней
- Еженедельные: 90 дней

Изменить можно в `app/Providers/AppServiceProvider.php`:
```php
$schedule->command('db:backup --keep=60') // 60 дней
    ->dailyAt('02:00');
```

## Резервное копирование файлов

Помимо базы данных, рекомендуется также создавать резервные копии:

- `storage/app/public/` - загруженные файлы
- `.env` - конфигурация
- `storage/logs/` - логи приложения

### Пример скрипта для полного резервного копирования

```bash
#!/bin/bash
# backup-full.sh

# Резервная копия БД
php artisan db:backup

# Резервная копия файлов
tar -czf storage/app/backups/files_$(date +%Y-%m-%d_%H%M%S).tar.gz \
    storage/app/public \
    storage/logs \
    .env

echo "Полное резервное копирование завершено"
```

## Восстановление на новом сервере

1. **Восстановите файлы проекта**
2. **Восстановите базу данных** (см. выше)
3. **Установите зависимости:**
   ```bash
   composer install
   npm install
   ```
4. **Настройте .env файл**
5. **Примените миграции:**
   ```bash
   php artisan migrate
   ```
6. **Очистите кеш:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

## Автоматизация резервного копирования

### Настройка cron для дополнительных резервных копий

Добавьте в crontab:
```bash
# Ежедневное резервное копирование в 3:00
0 3 * * * cd /path/to/project && php artisan db:backup >> /dev/null 2>&1
```

### Отправка резервных копий на удаленный сервер

Можно настроить автоматическую отправку резервных копий на удаленный сервер через SCP, FTP или облачное хранилище (S3, Google Drive и т.д.).

## Мониторинг резервных копий

Проверяйте логи для подтверждения успешного создания резервных копий:
```bash
tail -f storage/logs/laravel.log | grep "Резервная копия БД создана"
```

## Важные замечания

1. **Всегда тестируйте восстановление** на тестовой среде перед применением на продакшене
2. **Храните резервные копии в безопасном месте** (не на том же сервере)
3. **Шифруйте резервные копии**, содержащие персональные данные
4. **Регулярно проверяйте целостность** резервных копий
5. **Документируйте процесс восстановления** для вашей команды

