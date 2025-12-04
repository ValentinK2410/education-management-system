#!/bin/bash

# Команды для диагностики storage на сервере
# Выполните эти команды на сервере через SSH

echo "Выполните следующие команды на сервере:"
echo ""
echo "cd /var/www/www-root/data/www/m.dekan.pro"
echo ""
echo "# 1. Проверка симлинка"
echo "ls -la public/storage"
echo "readlink -f public/storage"
echo ""
echo "# 2. Проверка файлов"
echo "ls -la storage/app/public/avatars/ | head -5"
echo ""
echo "# 3. Проверка доступности через симлинк"
echo "TEST_FILE=\$(ls storage/app/public/avatars/ 2>/dev/null | head -1)"
echo "if [ -n \"\$TEST_FILE\" ]; then"
echo "    echo \"Тестовый файл: \$TEST_FILE\""
echo "    ls -la public/storage/avatars/\$TEST_FILE"
echo "fi"
echo ""
echo "# 4. Проверка конфигурации Nginx"
echo "grep -A 10 'location /storage' /etc/nginx/sites-available/m.dekan.pro || echo 'location /storage не найден'"
echo ""
echo "# 5. Проверка root в Nginx"
echo "grep 'root' /etc/nginx/sites-available/m.dekan.pro | head -3"














