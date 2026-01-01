<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Institution;
use App\Models\Program;
use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Контроллер для публичных страниц
 *
 * Обрабатывает отображение информации для неавторизованных пользователей.
 * Предоставляет доступ к каталогам учебных заведений, программ и курсов.
 */
class PublicController extends Controller
{
    /**
     * Отобразить главную страницу сайта
     *
     * Показывает краткую информацию о доступных учебных заведениях,
     * программах и курсах для привлечения посетителей.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Получение ограниченного количества записей для главной страницы
        $institutions = Institution::active()->with('programs')->take(6)->get();
        $programs = Program::active()->with('institution')->take(8)->get();
        $courses = Course::active()->with(['program.institution', 'instructor'])->take(8)->get();

        // Получаем последние события (опубликованные и будущие)
        $upcomingEvents = Event::published()
            ->upcoming()
            ->orderBy('start_date', 'asc')
            ->limit(3)
            ->get();

        // Получаем рекомендуемые события
        $featuredEvents = Event::published()
            ->featured()
            ->upcoming()
            ->orderBy('start_date', 'asc')
            ->limit(2)
            ->get();

        return view('public.index', compact('institutions', 'programs', 'courses', 'upcomingEvents', 'featuredEvents'));
    }

    /**
     * Отобразить список всех учебных заведений
     *
     * @return \Illuminate\View\View
     */
    public function institutions()
    {
        $institutions = Institution::active()->with('programs')->paginate(12);
        return view('public.institutions.index', compact('institutions'));
    }

    /**
     * Отобразить конкретное учебное заведение
     *
     * @param Institution $institution
     * @return \Illuminate\View\View
     */
    public function institution(Institution $institution)
    {
        $institution->load(['programs.courses']);
        return view('public.institutions.show', compact('institution'));
    }

    /**
     * Отобразить список всех образовательных программ
     *
     * @return \Illuminate\View\View
     */
    public function programs()
    {
        $programs = Program::active()->with(['institution', 'courses'])->paginate(12);
        return view('public.programs.index', compact('programs'));
    }

    /**
     * Отобразить конкретную образовательную программу
     *
     * @param Program $program
     * @return \Illuminate\View\View
     */
    public function program(Program $program)
    {
        $program->load(['institution', 'courses.instructor']);
        return view('public.programs.show', compact('program'));
    }

    /**
     * Отобразить список всех курсов
     *
     * @return \Illuminate\View\View
     */
    public function courses()
    {
        try {
            // Показываем активные курсы с программой
            $courses = Course::active()
                ->with(['program.institution', 'instructor'])
                ->whereHas('program')
                ->paginate(12);
            
            // Если активных курсов с программой нет, показываем все активные курсы
            if ($courses->isEmpty()) {
                $courses = Course::active()
                    ->with(['program.institution', 'instructor'])
                    ->paginate(12);
            }
            
            // Если и активных курсов нет, показываем все курсы (включая неактивные)
            if ($courses->isEmpty()) {
                $courses = Course::with(['program.institution', 'instructor'])
                    ->paginate(12);
            }
        } catch (\Exception $e) {
            // Если произошла ошибка, загружаем курсы без фильтров
            Log::error('Ошибка загрузки курсов: ' . $e->getMessage());
            $courses = Course::with(['program.institution', 'instructor'])->paginate(12);
        }
        return view('public.courses.index', compact('courses'));
    }

    /**
     * Отобразить конкретный курс
     *
     * @param Course $course
     * @return \Illuminate\View\View
     */
    public function course(Course $course)
    {
        $course->load(['program.institution', 'instructor']);
        return view('public.courses.show', compact('course'));
    }

    /**
     * Отобразить профиль преподавателя
     *
     * @param User $instructor
     * @return \Illuminate\View\View
     */
    public function instructor(User $instructor)
    {
        $instructor->load(['taughtCourses.program.institution']);
        return view('public.instructors.show', compact('instructor'));
    }
}
