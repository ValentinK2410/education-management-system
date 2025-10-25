#!/bin/bash

echo "🔧 Исправляем права доступа для Laravel..."

# Используем expect для автоматического ввода пароля
expect << 'EXPECT_SCRIPT'
spawn ssh -o StrictHostKeyChecking=no root@82.146.39.18
expect "password:"
send "lShzBqBqnsHcR2sUos8v4XT4\r"
expect "#"
send "cd /var/www/www-root/data/www/m.dekan.pro\r"
expect "#"
send "echo '🔐 Устанавливаем права доступа для storage...'\r"
expect "#"
send "chown -R www-data:www-data storage/\r"
expect "#"
send "chmod -R 775 storage/\r"
expect "#"
send "echo '🔐 Устанавливаем права доступа для bootstrap/cache...'\r"
expect "#"
send "chown -R www-data:www-data bootstrap/cache/\r"
expect "#"
send "chmod -R 775 bootstrap/cache/\r"
expect "#"
send "echo '🧹 Очищаем кэш...'\r"
expect "#"
send "php artisan view:clear\r"
expect "#"
send "php artisan config:clear\r"
expect "#"
send "php artisan cache:clear\r"
expect "#"
send "echo '✅ Права доступа исправлены!'\r"
expect "#"
send "exit\r"
expect eof
EXPECT_SCRIPT

echo "🌐 Права доступа исправлены на сервере!"
