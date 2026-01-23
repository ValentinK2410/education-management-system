<?php

namespace App\Services;

use App\Models\Group;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для синхронизации глобальных групп (cohorts) из Moodle
 * 
 * Синхронизирует cohorts из Moodle в локальную базу данных Laravel как группы
 */
class MoodleCohortSyncService
{
    /**
     * Сервис для работы с Moodle API
     * 
     * @var MoodleApiService
     */
    protected MoodleApiService $moodleApi;

    /**
     * Конструктор
     * 
     * @param MoodleApiService|null $moodleApi
     * @throws \InvalidArgumentException Если конфигурация Moodle некорректна
     */
    public function __construct(?MoodleApiService $moodleApi = null)
    {
        try {
            $this->moodleApi = $moodleApi ?? new MoodleApiService();
        } catch (\InvalidArgumentException $e) {
            Log::error('Ошибка инициализации MoodleApiService', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Синхронизировать все cohorts из Moodle
     * 
     * @return array Статистика синхронизации
     */
    public function syncCohorts(): array
    {
        $stats = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'errors_list' => []
        ];

        Log::info('Начало синхронизации cohorts из Moodle');

        try {
            // Получаем все cohorts из Moodle
            $moodleCohorts = $this->moodleApi->getCohorts();

            if ($moodleCohorts === false) {
                $stats['errors'] = 1;
                $errorMessage = 'Не удалось получить cohorts из Moodle API. Проверьте логи для деталей.';
                $stats['errors_list'][] = [
                    'type' => 'api_error',
                    'error' => $errorMessage,
                    'hint' => 'Возможные причины: отсутствие прав у токена на core_cohort_get_cohorts, неправильная конфигурация Moodle URL/токена, или cohorts отсутствуют в Moodle. См. документацию MOODLE_COHORTS_SETUP.md для решения проблемы.'
                ];
                Log::error('Ошибка получения cohorts из Moodle', [
                    'hint' => 'Проверьте логи MoodleApiService для детальной информации об ошибке. Если ошибка webservice_access_exception, добавьте функцию core_cohort_get_cohorts в права токена (см. MOODLE_COHORTS_SETUP.md)'
                ]);
                return $stats;
            }

            if (!is_array($moodleCohorts)) {
                $stats['errors'] = 1;
                $errorMessage = 'Moodle API вернул неожиданный формат данных: ' . gettype($moodleCohorts);
                $stats['errors_list'][] = [
                    'type' => 'format_error',
                    'error' => $errorMessage
                ];
                Log::warning('Moodle API вернул неожиданный формат данных', [
                    'type' => gettype($moodleCohorts),
                    'value' => $moodleCohorts
                ]);
                return $stats;
            }

            $stats['total'] = count($moodleCohorts);

            // Синхронизируем каждый cohort
            foreach ($moodleCohorts as $moodleCohort) {
                try {
                    $result = $this->syncCohort($moodleCohort);
                    
                    if ($result === 'created') {
                        $stats['created']++;
                    } elseif ($result === 'updated') {
                        $stats['updated']++;
                    } elseif ($result === 'skipped') {
                        $stats['skipped']++;
                    } else {
                        $stats['errors']++;
                        $stats['errors_list'][] = [
                            'type' => 'sync_error',
                            'cohort_id' => $moodleCohort['id'] ?? 'unknown',
                            'cohort_name' => $moodleCohort['name'] ?? 'unknown',
                            'error' => $result
                        ];
                    }
                } catch (\Exception $e) {
                    $stats['errors']++;
                    $stats['errors_list'][] = [
                        'type' => 'exception',
                        'cohort_id' => $moodleCohort['id'] ?? 'unknown',
                        'cohort_name' => $moodleCohort['name'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ];
                    Log::error('Ошибка синхронизации cohort', [
                        'cohort' => $moodleCohort,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Синхронизация cohorts завершена', [
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Критическая ошибка при синхронизации cohorts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $stats['errors']++;
            $stats['errors_list'][] = [
                'type' => 'critical_error',
                'error' => $e->getMessage()
            ];
        }

        return $stats;
    }

    /**
     * Синхронизировать один cohort из Moodle
     * 
     * @param array $moodleCohort Данные cohort из Moodle
     * @return string Результат синхронизации: 'created', 'updated', 'skipped' или сообщение об ошибке
     */
    public function syncCohort(array $moodleCohort): string
    {
        $moodleCohortId = $moodleCohort['id'] ?? null;
        
        if (!$moodleCohortId) {
            return 'Отсутствует ID cohort в данных Moodle';
        }

        // Ищем существующую группу по moodle_cohort_id
        $group = Group::where('moodle_cohort_id', $moodleCohortId)->first();

        // Подготавливаем данные для создания/обновления
        $groupData = [
            'moodle_cohort_id' => $moodleCohortId,
            'name' => $moodleCohort['name'] ?? 'Без названия',
            'description' => $moodleCohort['description'] ?? null,
            'is_active' => true,
            // Глобальные группы не привязаны к курсу или программе
            'course_id' => null,
            'program_id' => null,
        ];

        if ($group) {
            // Обновляем существующую группу
            $group->update($groupData);
            Log::info('Группа обновлена из Moodle cohort', [
                'group_id' => $group->id,
                'moodle_cohort_id' => $moodleCohortId,
                'name' => $groupData['name']
            ]);
            return 'updated';
        } else {
            // Создаем новую группу
            $group = Group::create($groupData);
            Log::info('Группа создана из Moodle cohort', [
                'group_id' => $group->id,
                'moodle_cohort_id' => $moodleCohortId,
                'name' => $groupData['name']
            ]);
            return 'created';
        }
    }
}
