# Руководство по миграции Education Management System на новый сервер

Это руководство содержит пошаговые инструкции по переносу всей системы на новый сервер.

## Быстрый старт (установка через GitHub)

Для быстрой установки используйте скрипт автоматической установки:

```bash
# На новом сервере
cd /tmp
git clone https://github.com/YOUR_USERNAME/education-management-system.git
cd education-management-system/scripts/migrate
chmod +x install-from-github.sh
sudo ./install-from-github.sh https://github.com/YOUR_USERNAME/education-management-system.git
```

После этого:
1. Настройте `.env` файл с параметрами базы данных
2. Импортируйте базу данных со старого сервера
3. Перенесите файлы из `storage/app/public`
4. Настройте веб-сервер

Подробные инструкции см. ниже.

## Содержание

1. [Подготовка](#подготовка)
2. [Экспорт данных со старого сервера](#экспорт-данных-со-старого-сервера)
3. [Настройка нового сервера](#настройка-нового-сервера)
4. [Импорт данных на новый сервер](#импорт-данных-на-новый-сервер)
5. [Перенос файлов](#перенос-файлов)
6. [Настройка веб-сервера](#настройка-веб-сервера)
7. [Тестирование](#тестирование)
8. [Откат изменений](#откат-изменений)

## Подготовка

### Требования к новому серверу

- **ОС**: Ubuntu 20.04+ или Debian 11+
- **PHP**: 8.2+ с расширениями: mbstring, xml, curl, zip, pdo_mysql, gd, fileinfo
- **MySQL**: 5.7+ или MariaDB 10.3+
- **Composer**: последняя версия
- **Git**: для клонирования репозитория
- **Nginx** или **Apache**: веб-сервер
- **SSL сертификат**: Let's Encrypt (рекомендуется)

### Установка необходимого ПО на новый сервер

```bash
# Обновление системы
sudo apt update && sudo apt upgrade -y

# Установка PHP и расширений
sudo apt install php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl \
    php8.2-zip php8.2-gd php8.2-mbstring php8.2-fileinfo -y

# Установка MySQL
sudo apt install mysql-server -y

# Установка Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Установка Git
sudo apt install git -y

# Установка Nginx
sudo apt install nginx -y

# Установка rsync (если еще не установлен)
sudo apt install rsync -y
```

### Проверка готовности сервера

Используйте скрипт проверки:

```bash
cd /path/to/education-management-system
chmod +x scripts/migrate/check-server-requirements.sh
./scripts/migrate/check-server-requirements.sh
```

## Экспорт данных со старого сервера

### 1. Создание резервной копии базы данных

**Вариант A: Использование скрипта (рекомендуется)**

```bash
cd /path/to/education-management-system
chmod +x scripts/migrate/export-database.sh
./scripts/migrate/export-database.sh
```

Скрипт запросит:
- Имя пользователя MySQL
- Пароль MySQL
- Имя базы данных
- Хост и порт MySQL

Резервная копия будет сохранена в `backups/migration/` с именем `[db_name]_[timestamp].sql.gz`

**Вариант B: Ручной экспорт**

```bash
# На старом сервере
mysqldump -u root -p education_system > /tmp/education_system_backup.sql
gzip /tmp/education_system_backup.sql
```

### 2. Экспорт списка таблиц (для проверки)

```bash
mysql -u root -p -e "SHOW TABLES FROM education_system;" > /tmp/tables_list.txt
```

## Настройка нового сервера

### Быстрая установка через GitHub (рекомендуется)

Для быстрой установки используйте скрипт `install-from-github.sh`:

```bash
cd /path/to/scripts/migrate
chmod +x install-from-github.sh
sudo ./install-from-github.sh https://github.com/YOUR_USERNAME/education-management-system.git
```

Этот скрипт выполнит все шаги автоматически. После этого переходите к разделу "Импорт данных на новый сервер".

### Ручная установка

### 1. Создание базы данных

```bash
# Подключение к MySQL
sudo mysql -u root -p

# В консоли MySQL:
CREATE DATABASE education_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'edu_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON education_system.* TO 'edu_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2. Установка через GitHub (рекомендуется)

**Вариант A: Использование скрипта автоматической установки**

```bash
cd /path/to/scripts/migrate
chmod +x install-from-github.sh
sudo ./install-from-github.sh https://github.com/YOUR_USERNAME/education-management-system.git
```

Скрипт автоматически:
- Клонирует репозиторий
- Установит зависимости Composer
- Создаст необходимые директории
- Настроит права доступа
- Создаст .env файл
- Сгенерирует APP_KEY
- Создаст символическую ссылку storage
- Оптимизирует приложение

**Вариант B: Ручная установка**

```bash
cd /var/www/www-root/data/www
git clone https://github.com/YOUR_USERNAME/education-management-system.git m.dekan.pro
cd m.dekan.pro
composer install --no-dev --optimize-autoloader
```

### 4. Настройка окружения

```bash
# Создание .env файла
cp .env.example .env

# Генерация APP_KEY
php artisan key:generate

# Редактирование .env
nano .env
```

**Важные параметры в .env:**

```env
APP_NAME="EduManage"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://m.dekan.pro

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=education_system
DB_USERNAME=edu_user
DB_PASSWORD=strong_password_here

# Настройки почты (если используются)
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=...
MAIL_USERNAME=...
MAIL_PASSWORD=...
```

### 5. Автоматическая настройка сервера

Используйте скрипт автоматической настройки:

```bash
cd /var/www/www-root/data/www/m.dekan.pro
chmod +x scripts/migrate/setup-new-server.sh
sudo ./scripts/migrate/setup-new-server.sh
```

Скрипт выполнит:
- Создание необходимых директорий
- Установку прав доступа
- Создание символической ссылки storage
- Очистку и оптимизацию кэша

## Импорт данных на новый сервер

### 1. Перенос файла резервной копии

**Вариант A: Через SCP**

```bash
# Со старого сервера
scp /tmp/education_system_backup.sql.gz user@new-server:/tmp/
```

**Вариант B: Через rsync**

```bash
# Со старого сервера
rsync -avz /tmp/education_system_backup.sql.gz user@new-server:/tmp/
```

### 2. Импорт базы данных

**Вариант A: Использование скрипта (рекомендуется)**

```bash
cd /var/www/www-root/data/www/m.dekan.pro
chmod +x scripts/migrate/import-database.sh
./scripts/migrate/import-database.sh /tmp/education_system_backup.sql.gz
```

**Вариант B: Ручной импорт**

```bash
# Распаковка
gunzip /tmp/education_system_backup.sql.gz

# Импорт
mysql -u edu_user -p education_system < /tmp/education_system_backup.sql
```

### 3. Проверка импорта

```bash
mysql -u edu_user -p education_system -e "SHOW TABLES;"
mysql -u edu_user -p education_system -e "SELECT COUNT(*) as users FROM users;"
```

## Перенос файлов

### Использование скрипта переноса

```bash
cd /path/to/education-management-system
chmod +x scripts/migrate/transfer-files.sh
./scripts/migrate/transfer-files.sh user@new-server
```

Скрипт перенесет:
- `storage/app/public/avatars/` - аватары пользователей
- `storage/app/public/certificate-templates/` - шаблоны сертификатов
- `storage/app/public/certificate-elements/` - элементы сертификатов
- Опционально: резервные копии базы данных

### Ручной перенос через rsync

```bash
# Со старого сервера
rsync -avz --progress \
    /var/www/www-root/data/www/m.dekan.pro/storage/app/public/ \
    user@new-server:/var/www/www-root/data/www/m.dekan.pro/storage/app/public/
```

### Проверка переноса файлов

```bash
# На новом сервере
ls -lh /var/www/www-root/data/www/m.dekan.pro/storage/app/public/avatars/
du -sh /var/www/www-root/data/www/m.dekan.pro/storage/app/public/*
```

## Настройка веб-сервера

### Настройка Nginx

Создайте файл `/etc/nginx/sites-available/m.dekan.pro`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name m.dekan.pro;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name m.dekan.pro;
    root /var/www/www-root/data/www/m.dekan.pro/public;

    index index.php;

    ssl_certificate /etc/letsencrypt/live/m.dekan.pro/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/m.dekan.pro/privkey.pem;

    location /storage {
        alias /var/www/www-root/data/www/m.dekan.pro/storage/app/public;
        try_files $uri $uri/ =404;
        access_log off;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Активация конфигурации:

```bash
sudo ln -s /etc/nginx/sites-available/m.dekan.pro /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Настройка SSL сертификата

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d m.dekan.pro
```

### Настройка автоматического обновления SSL

```bash
sudo certbot renew --dry-run
```

## Выполнение миграций (если нужно)

Если на новом сервере версия кода новее, чем на старом:

```bash
cd /var/www/www-root/data/www/m.dekan.pro
php artisan migrate --force
```

## Настройка автоматических резервных копий

Проверьте, что задача добавлена в crontab:

```bash
crontab -l | grep db:backup
```

Если нет, добавьте:

```bash
crontab -e
```

Добавьте строку:

```
0 2 * * * cd /var/www/www-root/data/www/m.dekan.pro && php artisan db:backup --keep=30
```

## Тестирование

### 1. Проверка доступности сайта

```bash
curl -I https://m.dekan.pro
```

### 2. Проверка подключения к базе данных

```bash
cd /var/www/www-root/data/www/m.dekan.pro
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

### 3. Проверка загрузки файлов

- Откройте https://m.dekan.pro
- Войдите в админ-панель
- Проверьте отображение аватаров пользователей
- Проверьте загрузку новых файлов

### 4. Проверка создания резервных копий

- Войдите в админ-панель → Резервные копии
- Создайте тестовую резервную копию
- Убедитесь, что она успешно создается

### 5. Проверка логов

```bash
tail -f /var/www/www-root/data/www/m.dekan.pro/storage/logs/laravel.log
```

## Обновление DNS

Если IP адрес сервера изменился, обновите A-запись в DNS:

```
m.dekan.pro A NEW_IP_ADDRESS
```

Время распространения DNS изменений: 5 минут - 48 часов (обычно 1-2 часа)

## Откат изменений

Если что-то пошло не так:

### 1. Вернуть DNS на старый сервер

Измените A-запись обратно на старый IP адрес.

### 2. Восстановить базу данных из резервной копии

```bash
mysql -u edu_user -p education_system < /tmp/education_system_backup.sql
```

### 3. Проверить логи для диагностики

```bash
tail -100 /var/www/www-root/data/www/m.dekan.pro/storage/logs/laravel.log
```

## Чек-лист после миграции

- [ ] Сайт доступен по HTTPS
- [ ] Авторизация работает
- [ ] Аватары пользователей отображаются
- [ ] Создание резервных копий работает
- [ ] Нет ошибок в логах
- [ ] Автоматические резервные копии настроены
- [ ] SSL сертификат настроен и обновляется автоматически
- [ ] Права доступа установлены правильно
- [ ] Символическая ссылка storage создана

## Важные замечания

1. **Резервные копии**: Всегда создавайте резервную копию перед миграцией
2. **Время простоя**: Миграция может занять 1-2 часа, планируйте время простоя
3. **Тестирование**: Протестируйте все функции после миграции
4. **Мониторинг**: Следите за логами первые несколько дней после миграции
5. **Безопасность**: Убедитесь, что пароли в .env файле надежные
6. **Права доступа**: Проверьте права доступа к storage и bootstrap/cache

## Получение помощи

Если возникли проблемы:

1. Проверьте логи: `storage/logs/laravel.log`
2. Проверьте логи веб-сервера: `/var/log/nginx/error.log`
3. Проверьте логи PHP-FPM: `/var/log/php8.2-fpm.log`
4. Используйте скрипт проверки: `scripts/migrate/check-server-requirements.sh`

## Дополнительные ресурсы

- [Laravel Deployment Documentation](https://laravel.com/docs/deployment)
- [Nginx Configuration Guide](https://nginx.org/en/docs/)
- [MySQL Backup and Recovery](https://dev.mysql.com/doc/refman/8.0/en/backup-and-recovery.html)
