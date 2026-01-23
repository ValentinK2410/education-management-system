<?php

namespace App\Services;

use App\Models\Group;
use App\Models\User;
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
     * @param bool $syncMembers Синхронизировать ли участников cohorts (по умолчанию true)
     * @return array Статистика синхронизации
     */
    public function syncCohorts(bool $syncMembers = true): array
    {
        $stats = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'members' => [
                'total' => 0,
                'added' => 0,
                'removed' => 0,
                'errors' => 0
            ],
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
                        continue; // Пропускаем синхронизацию участников при ошибке
                    }

                    // Синхронизируем участников cohort, если включено
                    if ($syncMembers) {
                        try {
                            $group = Group::where('moodle_cohort_id', $moodleCohort['id'])->first();
                            if ($group) {
                                $memberStats = $this->syncCohortMembers($group, $moodleCohort['id']);
                                $stats['members']['total'] += $memberStats['total'];
                                $stats['members']['added'] += $memberStats['added'];
                                $stats['members']['removed'] += $memberStats['removed'];
                                $stats['members']['errors'] += $memberStats['errors'];
                            }
                        } catch (\Exception $e) {
                            $stats['members']['errors']++;
                            Log::error('Ошибка синхронизации участников cohort', [
                                'cohort_id' => $moodleCohort['id'],
                                'error' => $e->getMessage()
                            ]);
                        }
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

    /**
     * Синхронизировать участников cohort в группу
     * 
     * @param Group $group Группа в системе деканата
     * @param int $moodleCohortId ID cohort в Moodle
     * @return array Статистика синхронизации участников
     */
    public function syncCohortMembers(Group $group, int $moodleCohortId): array
    {
        $stats = [
            'total' => 0,
            'added' => 0,
            'removed' => 0,
            'errors' => 0
        ];

        try {
            // Получаем участников cohort из Moodle
            $membersResult = $this->moodleApi->getCohortMembers([$moodleCohortId]);

            if ($membersResult === false) {
                Log::warning('Не удалось получить участников cohort', [
                    'cohort_id' => $moodleCohortId,
                    'group_id' => $group->id
                ]);
                $stats['errors'] = 1;
                return $stats;
            }

            // Находим участников для этого cohort
            $moodleUserIds = [];
            foreach ($membersResult as $cohortData) {
                if (isset($cohortData['cohortid']) && $cohortData['cohortid'] == $moodleCohortId) {
                    if (isset($cohortData['userids']) && is_array($cohortData['userids'])) {
                        $moodleUserIds = $cohortData['userids'];
                        break;
                    }
                }
            }

            // Если структура другая (прямой массив userids)
            if (empty($moodleUserIds) && isset($membersResult[0]['userids'])) {
                $moodleUserIds = $membersResult[0]['userids'];
            }

            $stats['total'] = count($moodleUserIds);

            if (empty($moodleUserIds)) {
                Log::info('Cohort не имеет участников', [
                    'cohort_id' => $moodleCohortId,
                    'group_id' => $group->id
                ]);
                // Удаляем всех участников из группы, если их нет в Moodle
                $currentMembersCount = $group->students()->count();
                if ($currentMembersCount > 0) {
                    $group->students()->detach();
                    $stats['removed'] = $currentMembersCount;
                }
                return $stats;
            }

            // Находим пользователей в системе деканата по moodle_user_id
            $users = User::whereIn('moodle_user_id', $moodleUserIds)
                ->whereNotNull('moodle_user_id')
                ->get()
                ->keyBy('moodle_user_id');

            // Получаем текущих участников группы с их moodle_user_id
            $currentMembers = $group->students()
                ->whereNotNull('moodle_user_id')
                ->get();
            
            // Создаем маппинг по moodle_user_id и по user_id для быстрой проверки
            $currentMembersByMoodleId = $currentMembers->keyBy('moodle_user_id');
            $currentMemberIds = $currentMembers->pluck('id')->toArray();

            Log::info('Начало синхронизации участников cohort', [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'cohort_id' => $moodleCohortId,
                'moodle_user_ids_count' => count($moodleUserIds),
                'found_users_count' => count($users),
                'current_members_count' => $currentMembers->count(),
                'current_member_ids' => $currentMemberIds
            ]);

            // Добавляем новых участников
            foreach ($moodleUserIds as $moodleUserId) {
                if (isset($users[$moodleUserId])) {
                    $user = $users[$moodleUserId];
                    
                    // Проверяем, не состоит ли уже пользователь в группе
                    $alreadyInGroup = $currentMembersByMoodleId->has($moodleUserId) || 
                                     in_array($user->id, $currentMemberIds);
                    
                    if (!$alreadyInGroup) {
                        try {
                            $group->students()->attach($user->id, [
                                'enrolled_at' => now()
                            ]);
                            $stats['added']++;
                            Log::info('Пользователь добавлен в группу из cohort', [
                                'user_id' => $user->id,
                                'user_name' => $user->name,
                                'moodle_user_id' => $moodleUserId,
                                'group_id' => $group->id,
                                'group_name' => $group->name
                            ]);
                        } catch (\Exception $e) {
                            $stats['errors']++;
                            Log::error('Ошибка добавления пользователя в группу', [
                                'user_id' => $user->id,
                                'group_id' => $group->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                } else {
                    // Пользователь не найден в системе деканата
                    Log::warning('Пользователь из cohort не найден в системе деканата', [
                        'moodle_user_id' => $moodleUserId,
                        'group_id' => $group->id,
                        'group_name' => $group->name,
                        'hint' => 'У пользователя в Moodle нет соответствующей записи в системе деканата с таким moodle_user_id'
                    ]);
                }
            }

            // Удаляем участников, которых нет в Moodle cohort
            foreach ($currentMembers as $user) {
                $moodleUserId = $user->moodle_user_id;
                if ($moodleUserId && !in_array($moodleUserId, $moodleUserIds)) {
                    try {
                        $group->students()->detach($user->id);
                        $stats['removed']++;
                        Log::debug('Пользователь удален из группы (отсутствует в cohort)', [
                            'user_id' => $user->id,
                            'moodle_user_id' => $moodleUserId,
                            'group_id' => $group->id
                        ]);
                    } catch (\Exception $e) {
                        $stats['errors']++;
                        Log::error('Ошибка удаления пользователя из группы', [
                            'user_id' => $user->id,
                            'group_id' => $group->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            Log::info('Синхронизация участников cohort завершена', [
                'group_id' => $group->id,
                'cohort_id' => $moodleCohortId,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка синхронизации участников cohort', [
                'group_id' => $group->id,
                'cohort_id' => $moodleCohortId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $stats['errors']++;
        }

        return $stats;
    }
}
