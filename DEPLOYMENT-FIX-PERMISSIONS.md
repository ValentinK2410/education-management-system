# Исправление прав доступа для m.dekan.pro

## ✅ Решение проблемы с правами 777

Сайт работает с правами 777 для директорий `storage/` и `bootstrap/cache/`.

### Команды для установки прав 777:

```bash
cd /var/www/www-root/data/www/m.dekan.pro

# Удалить скомпилированные представления
rm -rf storage/framework/views/*

# Создать необходимые директории
mkdir -p storage/framework/views
mkdir -p storage/framework/sessions
mkdir -p storage/framework/cache/data
mkdir -p storage/logs

# Установить права 777
chmod -R 777 storage/
chmod -R 777 bootstrap/cache/

# Назначить владельца
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/

# Создать файл логов
touch storage/logs/laravel.log
chmod 777 storage/logs/laravel.log
chown www-data:www-data storage/logs/laravel.log

# Очистить кэш
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
```

### Используйте скрипт для быстрого исправления:

```bash
cd /var/www/www-root/data/www/m.dekan.pro
git pull origin main
bash fix-permissions-777.sh
```

### ⚠️ Примечание о безопасности:

Права 777 означают полный доступ для всех пользователей. Это не безопасно для продакшена, но работает для решения проблем с правами доступа.

Для улучшения безопасности после проверки рекомендуется:

```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

Но если работает с 777, можно оставить как есть.
