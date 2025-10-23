<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Institution;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;

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

        return view('public.index', compact('institutions', 'programs', 'courses'));
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
        $courses = Course::active()->with(['program.institution', 'instructor'])->paginate(12);
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
