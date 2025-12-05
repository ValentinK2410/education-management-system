<?php
// Тестовый скрипт для проверки прав доступа через веб-сервер
// Откройте: https://m.dekan.pro/test-views-write.php

header('Content-Type: text/plain; charset=utf-8');

$viewsDir = dirname(__DIR__) . '/storage/framework/views';

echo "🔍 Тест записи в storage/framework/views через веб-сервер\n";
echo "==================================================\n\n";

// Информация о текущем пользователе
echo "🔧 Информация о PHP:\n";
echo "   Пользователь PHP (get_current_user): " . get_current_user() . "\n";
echo "   UID (posix_geteuid): " . posix_geteuid() . "\n";
echo "   GID (posix_getegid): " . posix_getegid() . "\n";
if (function_exists('posix_getpwuid')) {
    $userInfo = posix_getpwuid(posix_geteuid());
    echo "   Имя пользователя (posix_getpwuid): " . ($userInfo['name'] ?? 'неизвестно') . "\n";
}
if (function_exists('posix_getgrgid')) {
    $groupInfo = posix_getgrgid(posix_getegid());
    echo "   Группа (posix_getgrgid): " . ($groupInfo['name'] ?? 'неизвестно') . "\n";
}
echo "   PHP_SAPI: " . php_sapi_name() . "\n";
echo "\n";

// Проверяем существование директории
if (!is_dir($viewsDir)) {
    echo "❌ Директория не существует: $viewsDir\n";
    exit(1);
}

echo "✅ Директория существует: $viewsDir\n";

// Проверяем права доступа
$perms = substr(sprintf('%o', fileperms($viewsDir)), -4);
echo "📋 Текущие права доступа: $perms\n";

// Проверяем владельца
$owner = posix_getpwuid(fileowner($viewsDir));
$group = posix_getgrgid(filegroup($viewsDir));
echo "👤 Владелец директории: {$owner['name']} ({$owner['uid']})\n";
echo "👥 Группа директории: {$group['name']} ({$group['gid']})\n\n";

// Проверяем возможность записи
if (is_writable($viewsDir)) {
    echo "✅ Директория доступна для записи (is_writable)\n";
} else {
    echo "❌ Директория НЕ доступна для записи (is_writable)\n";
}

// Пробуем создать тестовый файл
$testFile = $viewsDir . '/test_web_' . time() . '.php';
echo "\n📝 Попытка создать файл: " . basename($testFile) . "\n";

$testContent = '<?php // test file created by web server';
$result = @file_put_contents($testFile, $testContent);

if ($result !== false) {
    echo "✅ Успешно создан файл!\n";
    echo "   Размер: $result байт\n";

    // Проверяем владельца созданного файла
    if (file_exists($testFile)) {
        $fileOwner = posix_getpwuid(fileowner($testFile));
        echo "   Владелец файла: {$fileOwner['name']} ({$fileOwner['uid']})\n";

        // Удаляем файл
        if (unlink($testFile)) {
            echo "✅ Файл успешно удален\n";
        } else {
            echo "⚠️  Файл создан, но не удалось удалить\n";
        }
    }
} else {
    echo "❌ Не удалось создать файл!\n";
    $error = error_get_last();
    if ($error) {
        echo "   Ошибка: " . $error['message'] . "\n";
        echo "   Файл: " . $error['file'] . "\n";
        echo "   Строка: " . $error['line'] . "\n";
    }
}

echo "\n✅ Тест завершен\n";
