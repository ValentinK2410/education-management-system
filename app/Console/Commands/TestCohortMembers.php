<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\User;
use App\Services\MoodleApiService;
use Illuminate\Console\Command;

/**
 * Команда для тестирования синхронизации участников cohorts
 */
class TestCohortMembers extends Command
{
    protected $signature = 'moodle:test-cohort-members {--cohort-id= : ID cohort в Moodle} {--group-id= : ID группы в системе деканата}';

    protected $description = 'Тестировать получение участников cohort из Moodle и их сопоставление с пользователями';

    public function handle()
    {
        $cohortId = $this->option('cohort-id');
        $groupId = $this->option('group-id');

        if (!$cohortId && !$groupId) {
            $this->error('Необходимо указать --cohort-id или --group-id');
            return Command::FAILURE;
        }

        try {
            $moodleApi = new MoodleApiService();
        } catch (\InvalidArgumentException $e) {
            $this->error('Ошибка конфигурации Moodle: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // Если указан group-id, получаем cohort-id из группы
        if ($groupId && !$cohortId) {
            $group = Group::find($groupId);
            if (!$group) {
                $this->error("Группа с ID {$groupId} не найдена");
                return Command::FAILURE;
            }
            if (!$group->moodle_cohort_id) {
                $this->error("У группы нет moodle_cohort_id");
                return Command::FAILURE;
            }
            $cohortId = $group->moodle_cohort_id;
            $this->info("Найдена группа: {$group->name} (cohort ID: {$cohortId})");
        }

        $this->info("=== Тестирование участников cohort ===");
        $this->info("Cohort ID (Moodle): {$cohortId}");
        $this->newLine();

        // Получаем участников из Moodle
        $this->info("1. Получение участников cohort из Moodle...");
        $membersResult = $moodleApi->getCohortMembers([$cohortId]);

        if ($membersResult === false) {
            $this->error("   ❌ Не удалось получить участников");
            return Command::FAILURE;
        }

        if (empty($membersResult)) {
            $this->warn("   ⚠️  Участники не найдены (cohort пустой)");
            return Command::SUCCESS;
        }

        // Извлекаем userids
        $moodleUserIds = [];
        foreach ($membersResult as $cohortData) {
            if (isset($cohortData['cohortid']) && $cohortData['cohortid'] == $cohortId) {
                if (isset($cohortData['userids']) && is_array($cohortData['userids'])) {
                    $moodleUserIds = $cohortData['userids'];
                    break;
                }
            }
        }

        if (empty($moodleUserIds) && isset($membersResult[0]['userids'])) {
            $moodleUserIds = $membersResult[0]['userids'];
        }

        $this->info("   ✅ Найдено участников в Moodle: " . count($moodleUserIds));
        if (count($moodleUserIds) > 0) {
            $this->line("   Первые 10 ID: " . implode(', ', array_slice($moodleUserIds, 0, 10)));
        }
        $this->newLine();

        // Проверяем сопоставление с пользователями
        $this->info("2. Сопоставление с пользователями в системе деканата...");
        $users = User::whereIn('moodle_user_id', $moodleUserIds)
            ->whereNotNull('moodle_user_id')
            ->get();

        $this->info("   ✅ Найдено пользователей с moodle_user_id: " . $users->count());
        $this->info("   ⚠️  Не найдено пользователей: " . (count($moodleUserIds) - $users->count()));

        if ($users->count() > 0) {
            $this->newLine();
            $this->info("   Примеры найденных пользователей:");
            foreach ($users->take(5) as $user) {
                $this->line("      - ID: {$user->id}, Имя: {$user->name}, Email: {$user->email}, Moodle ID: {$user->moodle_user_id}");
            }
        }

        if (count($moodleUserIds) > $users->count()) {
            $this->newLine();
            $this->warn("   Пользователи без moodle_user_id в системе:");
            $foundIds = $users->pluck('moodle_user_id')->toArray();
            $missingIds = array_diff($moodleUserIds, $foundIds);
            $this->line("   Первые 10 отсутствующих Moodle ID: " . implode(', ', array_slice($missingIds, 0, 10)));
        }

        // Если указана группа, проверяем текущих участников
        if ($groupId) {
            $group = Group::find($groupId);
            $this->newLine();
            $this->info("3. Проверка текущих участников группы...");
            $currentMembers = $group->students()->get();
            $this->info("   Текущих участников в группе: " . $currentMembers->count());
            
            if ($currentMembers->count() > 0) {
                $this->line("   Примеры участников:");
                foreach ($currentMembers->take(5) as $member) {
                    $moodleId = $member->moodle_user_id ?? 'нет';
                    $this->line("      - ID: {$member->id}, Имя: {$member->name}, Moodle ID: {$moodleId}");
                }
            }

            // Проверяем, какие пользователи должны быть добавлены
            $this->newLine();
            $this->info("4. Анализ: какие пользователи должны быть добавлены...");
            $currentMemberIds = $currentMembers->pluck('id')->toArray();
            $currentMoodleIds = $currentMembers->whereNotNull('moodle_user_id')->pluck('moodle_user_id')->toArray();
            
            $toAdd = [];
            foreach ($moodleUserIds as $moodleUserId) {
                if (isset($users[$moodleUserId])) {
                    $user = $users[$moodleUserId];
                    if (!in_array($user->id, $currentMemberIds) && !in_array($moodleUserId, $currentMoodleIds)) {
                        $toAdd[] = $user;
                    }
                }
            }
            
            if (count($toAdd) > 0) {
                $this->warn("   ⚠️  Пользователей для добавления: " . count($toAdd));
                $this->line("   Примеры:");
                foreach (array_slice($toAdd, 0, 5) as $user) {
                    $this->line("      - ID: {$user->id}, Имя: {$user->name}, Moodle ID: {$user->moodle_user_id}");
                }
            } else {
                $this->info("   ✅ Все найденные пользователи уже в группе");
            }
        } else {
            // Если не указана группа, ищем группу по cohort_id
            $group = Group::where('moodle_cohort_id', $cohortId)->first();
            if ($group) {
                $this->newLine();
                $this->info("3. Найдена группа: {$group->name} (ID: {$group->id})");
                $this->info("   Запустите команду с --group-id={$group->id} для детального анализа");
            }
        }

        $this->newLine();
        $this->info("=== Тестирование завершено ===");

        return Command::SUCCESS;
    }
}
