<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\StudentActivityProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstructorStatsController extends Controller
{
    /**
     * Показать список всех преподавателей со статистикой
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Получаем всех пользователей с ролью преподавателя
        $instructors = User::whereHas('roles', function ($query) {
            $query->where('slug', 'instructor');
        })
        ->with(['taughtCourses' => function ($query) {
            $query->withCount(['users' => function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('slug', 'student');
                });
            }]);
        }])
        ->get()
        ->map(function ($instructor) {
            // Подсчитываем статистику для каждого преподавателя
            $courses = $instructor->taughtCourses;
            $totalCourses = $courses->count();
            
            // Общее количество студентов (сумма по всем курсам, с возможными дубликатами)
            $totalStudentsAll = $courses->sum('users_count');
            
            // Получаем уникальных студентов по всем курсам преподавателя
            $courseIds = $courses->pluck('id');
            $uniqueStudents = 0;
            if ($courseIds->isNotEmpty()) {
                $uniqueStudents = \DB::table('user_courses')
                    ->whereIn('course_id', $courseIds)
                    ->join('users', 'user_courses.user_id', '=', 'users.id')
                    ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                    ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                    ->where('roles.slug', 'student')
                    ->distinct('user_courses.user_id')
                    ->count('user_courses.user_id');
            }
            
            // Подсчитываем проверенные работы
            // Если graded_by_user_id заполнен, проверяем его
            // Если нет, но курс принадлежит преподавателю и статус 'graded', считаем проверенным преподавателем
            $gradedActivities = StudentActivityProgress::whereHas('course', function ($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id);
            })
            ->where('status', 'graded')
            ->where(function ($query) use ($instructor) {
                $query->where('graded_by_user_id', $instructor->id)
                      ->orWhereNull('graded_by_user_id');
            })
            ->count();
            
            // Подсчитываем непроверенные работы
            $pendingActivities = StudentActivityProgress::whereHas('course', function ($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id);
            })
            ->where('status', 'submitted')
            ->count();
            
            return [
                'id' => $instructor->id,
                'name' => $instructor->name,
                'email' => $instructor->email,
                'photo' => $instructor->photo,
                'total_courses' => $totalCourses,
                'total_students_all' => $totalStudentsAll,
                'unique_students' => $uniqueStudents,
                'graded_activities' => $gradedActivities,
                'pending_activities' => $pendingActivities,
            ];
        });
        
        return view('admin.instructor-stats.index', compact('instructors'));
    }
    
    /**
     * Показать детальную статистику конкретного преподавателя
     *
     * @param User $instructor
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function show(User $instructor, Request $request)
    {
        // Проверяем, что пользователь является преподавателем
        if (!$instructor->hasRole('instructor')) {
            abort(404, 'Пользователь не является преподавателем');
        }
        
        // Получаем фильтры по дате
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        
        // Получаем все курсы преподавателя с количеством студентов
        $courses = Course::where('instructor_id', $instructor->id)
            ->with(['program', 'instructor'])
            ->withCount(['users' => function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('slug', 'student');
                });
            }])
            ->get();
        
        // Получаем все проверенные работы преподавателя
        // Если graded_by_user_id заполнен, проверяем его
        // Если нет, но курс принадлежит преподавателю и статус 'graded', считаем проверенным преподавателем
        $gradedActivitiesQuery = StudentActivityProgress::whereHas('course', function ($query) use ($instructor) {
            $query->where('instructor_id', $instructor->id);
        })
        ->where('status', 'graded')
        ->where(function ($query) use ($instructor) {
            $query->where('graded_by_user_id', $instructor->id)
                  ->orWhereNull('graded_by_user_id');
        });
        
        // Применяем фильтр по дате проверки
        if ($dateFrom) {
            $gradedActivitiesQuery->whereDate('graded_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $gradedActivitiesQuery->whereDate('graded_at', '<=', $dateTo);
        }
        
        $gradedActivities = $gradedActivitiesQuery
            ->with(['user', 'course', 'activity', 'gradedBy'])
            ->orderBy('graded_at', 'desc')
            ->get();
        
        // Получаем непроверенные работы
        $pendingActivities = StudentActivityProgress::whereHas('course', function ($query) use ($instructor) {
            $query->where('instructor_id', $instructor->id);
        })
        ->where('status', 'submitted')
        ->with(['user', 'course', 'activity'])
        ->orderBy('submitted_at', 'desc')
        ->get();
        
        // Статистика по типам активностей
        // Если graded_by_user_id заполнен, проверяем его
        // Если нет, но курс принадлежит преподавателю и статус 'graded', считаем проверенным преподавателем
        $activityStatsQuery = StudentActivityProgress::whereHas('course', function ($query) use ($instructor) {
            $query->where('instructor_id', $instructor->id);
        })
        ->where('status', 'graded')
        ->where(function ($query) use ($instructor) {
            $query->where('graded_by_user_id', $instructor->id)
                  ->orWhereNull('graded_by_user_id');
        });
        
        // Применяем фильтр по дате проверки
        if ($dateFrom) {
            $activityStatsQuery->whereDate('graded_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $activityStatsQuery->whereDate('graded_at', '<=', $dateTo);
        }
        
        $activityStats = $activityStatsQuery
            ->join('course_activities', 'student_activity_progress.activity_id', '=', 'course_activities.id')
            ->select('course_activities.activity_type', DB::raw('COUNT(*) as count'))
            ->groupBy('course_activities.activity_type')
            ->get()
            ->pluck('count', 'activity_type');
        
        // Общее количество студентов (сумма по всем курсам)
        $totalStudentsAll = $courses->sum('users_count');
        
        // Получаем уникальных студентов по всем курсам преподавателя
        $courseIds = $courses->pluck('id');
        $uniqueStudentsCount = 0;
        if ($courseIds->isNotEmpty()) {
            $uniqueStudentsCount = \DB::table('user_courses')
                ->whereIn('course_id', $courseIds)
                ->join('users', 'user_courses.user_id', '=', 'users.id')
                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where('roles.slug', 'student')
                ->distinct('user_courses.user_id')
                ->count('user_courses.user_id');
        }
        
        // Общая статистика
        $stats = [
            'total_courses' => $courses->count(),
            'total_students_all' => $totalStudentsAll,
            'unique_students' => $uniqueStudentsCount,
            'total_graded' => $gradedActivities->count(),
            'total_pending' => $pendingActivities->count(),
            'assignments_graded' => $activityStats->get('assign', 0),
            'quizzes_graded' => $activityStats->get('quiz', 0),
            'forums_graded' => $activityStats->get('forum', 0),
        ];
        
        return view('admin.instructor-stats.show', compact('instructor', 'courses', 'gradedActivities', 'pendingActivities', 'stats', 'dateFrom', 'dateTo'));
    }
}

