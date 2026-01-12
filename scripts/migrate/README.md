# Скрипты для миграции системы

Эта директория содержит скрипты для автоматизации процесса миграции Education Management System на новый сервер.

## Доступные скрипты

### 1. `install-from-github.sh` ⭐ РЕКОМЕНДУЕТСЯ

Полная установка системы через GitHub. Выполняет все необходимые шаги автоматически.

**Использование:**
```bash
chmod +x install-from-github.sh
sudo ./install-from-github.sh [github-url] [install-path]
```

**Пример:**
```bash
sudo ./install-from-github.sh https://github.com/username/education-management-system.git
```

Скрипт автоматически:
- Клонирует репозиторий из GitHub
- Устанавливает зависимости Composer
- Создает необходимые директории
- Настраивает права доступа
- Создает .env файл
- Генерирует APP_KEY
- Создает символическую ссылку storage
- Оптимизирует приложение

### 2. `check-server-requirements.sh`

Проверяет готовность сервера для миграции. Проверяет наличие и версии:
- PHP 8.2+ и необходимых расширений
- MySQL/MariaDB
- Composer
- Git
- rsync
- Веб-сервер (Nginx/Apache)
- PHP-FPM

**Использование:**
```bash
chmod +x check-server-requirements.sh
./check-server-requirements.sh
```

### 3. `export-database.sh`

Экспортирует базу данных со старого сервера в сжатый SQL файл.

**Использование:**
```bash
chmod +x export-database.sh
./export-database.sh
```

Скрипт запросит:
- Имя пользователя MySQL
- Пароль MySQL
- Имя базы данных
- Хост и порт MySQL

Резервная копия будет сохранена в `backups/migration/[db_name]_[timestamp].sql.gz`

### 4. `import-database.sh`

Импортирует базу данных на новый сервер из файла резервной копии.

**Использование:**
```bash
chmod +x import-database.sh
./import-database.sh [путь_к_файлу.sql.gz]
```

Если путь не указан, скрипт запросит его интерактивно.

### 5. `transfer-files.sh`

Переносит загруженные файлы со старого сервера на новый через rsync.

**Использование:**
```bash
chmod +x transfer-files.sh
./transfer-files.sh [user@new-server]
```

Переносит:
- `storage/app/public/avatars/` - аватары пользователей
- `storage/app/public/certificate-templates/` - шаблоны сертификатов
- `storage/app/public/certificate-elements/` - элементы сертификатов
- Опционально: резервные копии базы данных

### 6. `setup-new-server.sh`

Автоматически настраивает новый сервер после клонирования репозитория.

**Использование:**
```bash
chmod +x setup-new-server.sh
sudo ./setup-new-server.sh
```

Выполняет:
- Создание необходимых директорий
- Установку прав доступа
- Создание символической ссылки storage
- Очистку и оптимизацию кэша
- Проверку и генерацию APP_KEY

**Важно:** Скрипт должен запускаться от root или с sudo.

## Порядок использования

### На старом сервере:

1. **Проверка готовности:**
   ```bash
   ./check-server-requirements.sh
   ```

2. **Экспорт базы данных:**
   ```bash
   ./export-database.sh
   ```

3. **Перенос файлов:**
   ```bash
   ./transfer-files.sh user@new-server
   ```

### На новом сервере:

1. **Установка ПО** (см. MIGRATION_GUIDE.md)

2. **Полная установка через GitHub (рекомендуется):**
   ```bash
   cd /path/to/scripts/migrate
   chmod +x install-from-github.sh
   sudo ./install-from-github.sh https://github.com/YOUR_USERNAME/education-management-system.git
   ```
   
   Или **ручная установка:**
   ```bash
   cd /var/www/www-root/data/www
   git clone https://github.com/YOUR_USERNAME/education-management-system.git m.dekan.pro
   cd m.dekan.pro
   composer install --no-dev --optimize-autoloader
   sudo ./scripts/migrate/setup-new-server.sh
   ```

3. **Настройка окружения:**
   ```bash
   nano /var/www/www-root/data/www/m.dekan.pro/.env
   # Настройте параметры БД и другие настройки
   ```

6. **Импорт базы данных:**
   ```bash
   ./scripts/migrate/import-database.sh /tmp/education_system_backup.sql.gz
   ```

7. **Настройка веб-сервера** (см. MIGRATION_GUIDE.md)

8. **Тестирование** (см. MIGRATION_GUIDE.md)

## Требования

- Bash 4.0+
- Доступ к MySQL/MariaDB
- SSH доступ к серверам (для переноса файлов)
- Права root или sudo (для setup-new-server.sh)

## Безопасность

- Все скрипты запрашивают пароли интерактивно (не передают через командную строку)
- Рекомендуется использовать SSH ключи вместо паролей для rsync
- Убедитесь, что файлы резервных копий имеют правильные права доступа

## Устранение неполадок

### Ошибка "Permission denied"

Убедитесь, что скрипты имеют права на выполнение:
```bash
chmod +x *.sh
```

### Ошибка подключения к MySQL

Проверьте:
- Правильность учетных данных
- Доступность MySQL сервера
- Права пользователя MySQL

### Ошибка rsync

Проверьте:
- SSH доступ к новому серверу
- Правильность пути к проекту
- Права доступа к директориям

## Дополнительная информация

Подробное руководство по миграции см. в файле `MIGRATION_GUIDE.md` в корне проекта.

