# КРИТИЧЕСКОЕ ОБНОВЛЕНИЕ СЕРВЕРА

## Проблема:
На сервере отсутствуют контроллеры `CourseController` и `SettingsController`.

## Команды для выполнения на сервере:

```bash
cd /var/www/www-root/data/www/m.dekan.pro

# 1. Проверить текущее состояние
git status

# 2. Принудительно сбросить все локальные изменения
git reset --hard HEAD

# 3. Получить обновления с GitHub
git fetch origin
git reset --hard origin/main

# 4. Проверить, что файлы контроллеров существуют
ls -la app/Http/Controllers/Admin/CourseController.php
ls -la app/Http/Controllers/Admin/SettingsController.php

# 5. Если файлов нет, создать их вручную или скопировать с GitHub
# git checkout origin/main -- app/Http/Controllers/Admin/CourseController.php
# git checkout origin/main -- app/Http/Controllers/Admin/SettingsController.php

# 6. Удалить скомпилированные представления
rm -rf storage/framework/views/*

# 7. Установить права доступа
chmod -R 777 storage/
chmod -R 777 bootstrap/cache/

# 8. Очистить все кэши
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# 9. Проверить список маршрутов
php artisan route:list | grep courses

# 10. Перезапустить PHP-FPM (если доступно)
sudo service php8.4-fpm restart
```

## Проверка после обновления:

```bash
# Проверить, что контроллеры существуют
ls -la app/Http/Controllers/Admin/

# Проверить маршруты
php artisan route:list

# Проверить логи
tail -f storage/logs/laravel.log
```

## Если проблема сохраняется:

```bash
# Полная переустановка кода с GitHub
cd /var/www/www-root/data/www/m.dekan.pro
git stash
git fetch origin
git reset --hard origin/main
composer dump-autoload
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```
