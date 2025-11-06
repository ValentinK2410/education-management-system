# Быстрое обновление сервера

Выполните эти команды на сервере m.dekan.pro:

```bash
cd /var/www/www-root/data/www/m.dekan.pro
git pull origin main
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```

Проверьте, что данные обновились:
```bash
tail -20 app/Http/Controllers/Admin/ProgramController.php
```

В методе update должна быть строка:
```php
return redirect()->route('admin.programs.index')
```
