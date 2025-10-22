<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of courses.
     */
    public function index()
    {
        $courses = Course::with(['program.institution', 'instructor'])->paginate(15);
        return view('admin.courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new course.
     */
    public function create()
    {
        $programs = Program::active()->with('institution')->get();
        $instructors = User::whereHas('roles', function ($query) {
            $query->where('slug', 'instructor');
        })->get();
        
        return view('admin.courses.create', compact('programs', 'instructors'));
    }

    /**
     * Store a newly created course.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'program_id' => 'required|exists:programs,id',
            'instructor_id' => 'nullable|exists:users,id',
            'code' => 'nullable|string|max:20',
            'credits' => 'nullable|integer|min:0',
            'duration' => 'nullable|string|max:100',
            'schedule' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'prerequisites' => 'nullable|array',
            'prerequisites.*' => 'string',
            'learning_outcomes' => 'nullable|array',
            'learning_outcomes.*' => 'string',
        ]);

        Course::create($request->all());

        return redirect()->route('admin.courses.index')
            ->with('success', 'Курс успешно создан.');
    }

    /**
     * Display the specified course.
     */
    public function show(Course $course)
    {
        $course->load(['program.institution', 'instructor']);
        return view('admin.courses.show', compact('course'));
    }

    /**
     * Show the form for editing the course.
     */
    public function edit(Course $course)
    {
        $programs = Program::active()->with('institution')->get();
        $instructors = User::whereHas('roles', function ($query) {
            $query->where('slug', 'instructor');
        })->get();
        
        return view('admin.courses.edit', compact('course', 'programs', 'instructors'));
    }

    /**
     * Update the specified course.
     */
    public function update(Request $request, Course $course)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'program_id' => 'required|exists:programs,id',
            'instructor_id' => 'nullable|exists:users,id',
            'code' => 'nullable|string|max:20',
            'credits' => 'nullable|integer|min:0',
            'duration' => 'nullable|string|max:100',
            'schedule' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'prerequisites' => 'nullable|array',
            'prerequisites.*' => 'string',
            'learning_outcomes' => 'nullable|array',
            'learning_outcomes.*' => 'string',
            'is_active' => 'boolean',
        ]);

        $course->update($request->all());

        return redirect()->route('admin.courses.index')
            ->with('success', 'Курс успешно обновлен.');
    }

    /**
     * Remove the specified course.
     */
    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', 'Курс успешно удален.');
    }
}
