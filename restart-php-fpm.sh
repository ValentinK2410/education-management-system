#!/bin/bash
# Скрипт для автоматического определения и перезапуска PHP-FPM

echo "🔍 Поиск доступной версии PHP-FPM..."

# Проверяем разные версии PHP-FPM
if systemctl list-units | grep -q 'php8.4-fpm'; then
    echo "✅ Найдена версия 8.4"
    sudo systemctl restart php8.4-fpm
    sudo systemctl status php8.4-fpm
elif systemctl list-units | grep -q 'php8.3-fpm'; then
    echo "✅ Найдена версия 8.3"
    sudo systemctl restart php8.3-fpm
    sudo systemctl status php8.3-fpm
elif systemctl list-units | grep -q 'php8.2-fpm'; then
    echo "✅ Найдена версия 8.2"
    sudo systemctl restart php8.2-fpm
    sudo systemctl status php8.2-fpm
elif systemctl list-units | grep -q 'php8.1-fpm'; then
    echo "✅ Найдена версия 8.1"
    sudo systemctl restart php8.1-fpm
    sudo systemctl status php8.1-fpm
elif systemctl list-units | grep -q 'php8.0-fpm'; then
    echo "✅ Найдена версия 8.0"
    sudo systemctl restart php8.0-fpm
    sudo systemctl status php8.0-fpm
elif systemctl list-units | grep -q 'php-fpm'; then
    echo "✅ Найдена общая версия PHP-FPM"
    sudo systemctl restart php-fpm
    sudo systemctl status php-fpm
else
    echo "❌ PHP-FPM не найден через systemctl"
    echo "Попробуйте вручную:"
    echo "  sudo service php-fpm restart"
    echo "  sudo service php8.3-fpm restart"
    echo "  sudo service php8.2-fpm restart"
fi
