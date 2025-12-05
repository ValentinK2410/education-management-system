<?php
// Скрипт для диагностики и исправления прав доступа к storage/framework/views
// Запустите: php fix-views-permissions.php

$viewsDir = __DIR__ . '/storage/framework/views';
$projectRoot = __DIR__;

echo "🔍 Диагностика прав доступа к storage/framework/views\n";
echo "==================================================\n\n";

// Проверяем существование директории
if (!is_dir($viewsDir)) {
    echo "❌ Директория не существует: $viewsDir\n";
    if (mkdir($viewsDir, 0775, true)) {
        echo "✅ Директория создана\n";
    } else {
        echo "❌ Не удалось создать директорию\n";
        exit(1);
    }
} else {
    echo "✅ Директория существует: $viewsDir\n";
}

// Проверяем права доступа
$perms = substr(sprintf('%o', fileperms($viewsDir)), -4);
echo "📋 Текущие права доступа: $perms\n";

// Проверяем владельца
$owner = posix_getpwuid(fileowner($viewsDir));
$group = posix_getgrgid(filegroup($viewsDir));
echo "👤 Владелец: {$owner['name']} ({$owner['uid']})\n";
echo "👥 Группа: {$group['name']} ({$group['gid']})\n";

// Проверяем возможность записи
if (is_writable($viewsDir)) {
    echo "✅ Директория доступна для записи\n";
} else {
    echo "❌ Директория НЕ доступна для записи\n";
}

// Пробуем создать тестовый файл
$testFile = $viewsDir . '/test_' . time() . '.php';
if (file_put_contents($testFile, '<?php // test')) {
    echo "✅ Успешно создан тестовый файл: " . basename($testFile) . "\n";
    unlink($testFile);
    echo "✅ Тестовый файл удален\n";
} else {
    echo "❌ Не удалось создать тестовый файл\n";
    echo "   Ошибка: " . error_get_last()['message'] . "\n";
}

// Проверяем родительские директории
echo "\n📁 Проверка родительских директорий:\n";
$dirs = [
    $projectRoot . '/storage',
    $projectRoot . '/storage/framework',
    $viewsDir
];

foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $owner = posix_getpwuid(fileowner($dir));
        $writable = is_writable($dir) ? '✅' : '❌';
        echo "   $writable $dir (права: $perms, владелец: {$owner['name']})\n";
    } else {
        echo "   ❌ $dir не существует\n";
    }
}

// Проверяем текущего пользователя PHP
echo "\n🔧 Информация о PHP:\n";
echo "   Пользователь PHP: " . get_current_user() . "\n";
echo "   UID: " . posix_geteuid() . "\n";
echo "   GID: " . posix_getegid() . "\n";

echo "\n✅ Диагностика завершена\n";
