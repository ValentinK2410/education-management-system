<?php

namespace App\Listeners;

use App\Events\UserCreated;
use App\Services\MoodleApiService;
use Illuminate\Support\Facades\Log;

/**
 * Слушатель для синхронизации пользователя с Moodle
 * 
 * Создает пользователя в Moodle при создании пользователя в Laravel
 */
class SyncUserToMoodle
{
    /**
     * Сервис для работы с Moodle API
     * 
     * @var MoodleApiService
     */
    protected MoodleApiService $moodleApi;

    /**
     * Создать экземпляр слушателя
     * 
     * @param MoodleApiService $moodleApi
     */
    public function __construct(MoodleApiService $moodleApi)
    {
        $this->moodleApi = $moodleApi;
    }

    /**
     * Обработать событие создания пользователя
     * 
     * @param UserCreated $event
     * @return void
     */
    public function handle(UserCreated $event): void
    {
        // Проверяем, включена ли синхронизация с Moodle
        if (!config('services.moodle.enabled', true)) {
            Log::info('Moodle sync disabled, skipping user sync', [
                'user_id' => $event->user->id,
                'email' => $event->user->email
            ]);
            return;
        }

        // Проверяем, что настроены URL и токен Moodle
        $moodleUrl = config('services.moodle.url');
        $moodleToken = config('services.moodle.token');

        if (empty($moodleUrl) || empty($moodleToken)) {
            Log::warning('Moodle sync skipped: URL or token not configured', [
                'user_id' => $event->user->id,
                'email' => $event->user->email
            ]);
            return;
        }

        try {
            // Проверяем, существует ли уже пользователь в Moodle
            $existingUser = $this->moodleApi->getUserByEmail($event->user->email);

            if ($existingUser) {
                // Пользователь уже существует, сохраняем его ID
                $event->user->update(['moodle_user_id' => $existingUser['id']]);
                Log::info('Moodle user already exists, linked to Laravel user', [
                    'user_id' => $event->user->id,
                    'moodle_user_id' => $existingUser['id'],
                    'email' => $event->user->email
                ]);
                return;
            }

            // Разбиваем имя на имя и фамилию
            $nameParts = explode(' ', $event->user->name, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '-';

            // Подготавливаем данные для создания пользователя в Moodle
            $moodleUserData = [
                'username' => $event->user->email, // Используем email как логин
                'password' => $event->plainPassword,
                'firstname' => $firstName,
                'lastname' => $lastName,
                'email' => $event->user->email,
            ];

            // Создаем пользователя в Moodle
            $moodleUser = $this->moodleApi->createUser($moodleUserData);

            if ($moodleUser && isset($moodleUser['id'])) {
                // Сохраняем ID пользователя Moodle в базе данных
                $event->user->update(['moodle_user_id' => $moodleUser['id']]);

                Log::info('User successfully synced to Moodle', [
                    'user_id' => $event->user->id,
                    'moodle_user_id' => $moodleUser['id'],
                    'email' => $event->user->email
                ]);
            } else {
                Log::error('Failed to create user in Moodle', [
                    'user_id' => $event->user->id,
                    'email' => $event->user->email,
                    'response' => $moodleUser
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception while syncing user to Moodle', [
                'user_id' => $event->user->id,
                'email' => $event->user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}

