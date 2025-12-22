<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Контроллер API для синхронизации пользователей из WordPress
 * 
 * Вызывается из WordPress плагина после успешного создания пользователя в Moodle
 */
class UserSyncController extends Controller
{
    /**
     * Создать пользователя в Laravel приложении
     * Вызывается из WordPress после создания пользователя в Moodle
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createFromWordPress(Request $request)
    {
        // Проверяем API токен для безопасности
        $apiToken = config('services.wordpress_api.token');
        if (empty($apiToken) || $request->header('X-API-Token') !== $apiToken) {
            Log::warning('Unauthorized API request to create user from WordPress', [
                'ip' => $request->ip(),
                'headers' => $request->headers->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // Валидация входящих данных
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'moodle_user_id' => 'required|integer',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed for WordPress user sync', [
                'errors' => $validator->errors()->toArray(),
                'data' => $request->except('password')
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Разбиваем имя на имя и фамилию
            $nameParts = explode(' ', $request->name, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            // Создание пользователя
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'moodle_user_id' => $request->moodle_user_id,
                'is_active' => true,
            ]);

            // Назначение роли студента по умолчанию
            $studentRole = Role::where('slug', 'student')->first();
            if ($studentRole) {
                $user->roles()->attach($studentRole);
            }

            Log::info('User successfully created from WordPress', [
                'user_id' => $user->id,
                'email' => $user->email,
                'moodle_user_id' => $user->moodle_user_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'moodle_user_id' => $user->moodle_user_id
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Exception while creating user from WordPress', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except('password')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

