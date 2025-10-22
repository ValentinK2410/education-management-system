<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Institution;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    /**
     * Display the homepage.
     */
    public function index()
    {
        $institutions = Institution::active()->with('programs')->take(6)->get();
        $programs = Program::active()->with('institution')->take(8)->get();
        $courses = Course::active()->with(['program.institution', 'instructor'])->take(8)->get();
        
        return view('public.index', compact('institutions', 'programs', 'courses'));
    }

    /**
     * Display a listing of institutions.
     */
    public function institutions()
    {
        $institutions = Institution::active()->with('programs')->paginate(12);
        return view('public.institutions.index', compact('institutions'));
    }

    /**
     * Display the specified institution.
     */
    public function institution(Institution $institution)
    {
        $institution->load(['programs.courses']);
        return view('public.institutions.show', compact('institution'));
    }

    /**
     * Display a listing of programs.
     */
    public function programs()
    {
        $programs = Program::active()->with(['institution', 'courses'])->paginate(12);
        return view('public.programs.index', compact('programs'));
    }

    /**
     * Display the specified program.
     */
    public function program(Program $program)
    {
        $program->load(['institution', 'courses.instructor']);
        return view('public.programs.show', compact('program'));
    }

    /**
     * Display a listing of courses.
     */
    public function courses()
    {
        $courses = Course::active()->with(['program.institution', 'instructor'])->paginate(12);
        return view('public.courses.index', compact('courses'));
    }

    /**
     * Display the specified course.
     */
    public function course(Course $course)
    {
        $course->load(['program.institution', 'instructor']);
        return view('public.courses.show', compact('course'));
    }

    /**
     * Display the specified instructor.
     */
    public function instructor(User $instructor)
    {
        $instructor->load(['taughtCourses.program.institution']);
        return view('public.instructors.show', compact('instructor'));
    }
}
