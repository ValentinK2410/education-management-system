<?php

namespace App\Traits;

/**
 * Trait для проверки зависимостей перед удалением
 */
trait ChecksDependencies
{
    /**
     * Получить список зависимостей модели
     *
     * @return array Массив с информацией о зависимостях
     */
    public function getDependencies(): array
    {
        $dependencies = [];

        // Переопределяется в моделях
        if (method_exists($this, 'checkDependencies')) {
            return $this->checkDependencies();
        }

        return $dependencies;
    }

    /**
     * Проверить, можно ли безопасно удалить модель
     *
     * @return array ['can_delete' => bool, 'dependencies' => array, 'message' => string]
     */
    public function canBeDeleted(): array
    {
        $dependencies = $this->getDependencies();
        $hasDependencies = !empty($dependencies);

        $message = '';
        if ($hasDependencies) {
            $counts = [];
            foreach ($dependencies as $dep) {
                $counts[] = "{$dep['name']}: {$dep['count']}";
            }
            $message = 'Невозможно удалить запись. Связанные данные: ' . implode(', ', $counts);
        }

        return [
            'can_delete' => !$hasDependencies,
            'dependencies' => $dependencies,
            'message' => $message ?: 'Запись может быть удалена'
        ];
    }
}

