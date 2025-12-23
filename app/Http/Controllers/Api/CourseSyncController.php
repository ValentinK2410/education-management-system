<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Контроллер API для синхронизации курсов из WordPress
 * 
 * Вызывается из WordPress плагина после успешной синхронизации курса из Moodle
 */
class CourseSyncController extends Controller
{
    /**
     * Создать или обновить курс в Laravel приложении
     * Вызывается из WordPress после синхронизации курса из Moodle
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncFromWordPress(Request $request)
    {
        // Проверяем API токен для безопасности
        $apiToken = config('services.wordpress_api.token');
        if (empty($apiToken) || $request->header('X-API-Token') !== $apiToken) {
            Log::warning('Unauthorized API request to sync course from WordPress', [
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
            'wordpress_course_id' => 'required|integer',
            'moodle_course_id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'category_id' => 'nullable|integer',
            'category_name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'duration' => 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|integer|min:0',
            'enrolled' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:publish,draft,pending,private',
            'action' => 'required|string|in:created,updated',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed for WordPress course sync', [
                'errors' => $validator->errors()->toArray(),
                'data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();
            
            // Ищем существующий курс по WordPress ID или Moodle ID
            $course = Course::where('wordpress_course_id', $data['wordpress_course_id'])
                ->orWhere(function($query) use ($data) {
                    if (!empty($data['moodle_course_id'])) {
                        $query->where('moodle_course_id', $data['moodle_course_id']);
                    }
                })
                ->first();
            
            // Подготавливаем данные для создания/обновления
            $courseData = [
                'wordpress_course_id' => $data['wordpress_course_id'],
                'moodle_course_id' => $data['moodle_course_id'] ?? null,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'duration' => $data['duration'] ?? null,
                'price' => $data['price'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'category_name' => $data['category_name'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'capacity' => $data['capacity'] ?? null,
                'enrolled' => $data['enrolled'] ?? 0,
                'is_active' => ($data['status'] ?? 'publish') === 'publish',
                'is_paid' => !empty($data['price']) && $data['price'] > 0,
            ];
            
            if ($course) {
                // Обновляем существующий курс
                $course->update($courseData);
                
                Log::info('Course successfully updated from WordPress', [
                    'course_id' => $course->id,
                    'wordpress_course_id' => $course->wordpress_course_id,
                    'moodle_course_id' => $course->moodle_course_id,
                    'name' => $course->name
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Course updated successfully',
                    'course' => [
                        'id' => $course->id,
                        'name' => $course->name,
                        'wordpress_course_id' => $course->wordpress_course_id,
                        'moodle_course_id' => $course->moodle_course_id
                    ]
                ], 200);
            } else {
                // Создаем новый курс
                $course = Course::create($courseData);
                
                Log::info('Course successfully created from WordPress', [
                    'course_id' => $course->id,
                    'wordpress_course_id' => $course->wordpress_course_id,
                    'moodle_course_id' => $course->moodle_course_id,
                    'name' => $course->name
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Course created successfully',
                    'course' => [
                        'id' => $course->id,
                        'name' => $course->name,
                        'wordpress_course_id' => $course->wordpress_course_id,
                        'moodle_course_id' => $course->moodle_course_id
                    ]
                ], 201);
            }

        } catch (\Exception $e) {
            Log::error('Exception while syncing course from WordPress', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync course',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

