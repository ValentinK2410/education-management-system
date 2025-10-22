<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /**
     * Display a listing of programs.
     */
    public function index()
    {
        $programs = Program::with(['institution', 'courses'])->paginate(15);
        return view('admin.programs.index', compact('programs'));
    }

    /**
     * Show the form for creating a new program.
     */
    public function create()
    {
        $institutions = Institution::active()->get();
        return view('admin.programs.create', compact('institutions'));
    }

    /**
     * Store a newly created program.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'institution_id' => 'required|exists:institutions,id',
            'duration' => 'nullable|string|max:100',
            'degree_level' => 'nullable|string|max:100',
            'tuition_fee' => 'nullable|numeric|min:0',
            'language' => 'required|string|max:10',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string',
        ]);

        Program::create($request->all());

        return redirect()->route('admin.programs.index')
            ->with('success', 'Учебная программа успешно создана.');
    }

    /**
     * Display the specified program.
     */
    public function show(Program $program)
    {
        $program->load(['institution', 'courses.instructor']);
        return view('admin.programs.show', compact('program'));
    }

    /**
     * Show the form for editing the program.
     */
    public function edit(Program $program)
    {
        $institutions = Institution::active()->get();
        return view('admin.programs.edit', compact('program', 'institutions'));
    }

    /**
     * Update the specified program.
     */
    public function update(Request $request, Program $program)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'institution_id' => 'required|exists:institutions,id',
            'duration' => 'nullable|string|max:100',
            'degree_level' => 'nullable|string|max:100',
            'tuition_fee' => 'nullable|numeric|min:0',
            'language' => 'required|string|max:10',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string',
            'is_active' => 'boolean',
        ]);

        $program->update($request->all());

        return redirect()->route('admin.programs.index')
            ->with('success', 'Учебная программа успешно обновлена.');
    }

    /**
     * Remove the specified program.
     */
    public function destroy(Program $program)
    {
        $program->delete();

        return redirect()->route('admin.programs.index')
            ->with('success', 'Учебная программа успешно удалена.');
    }
}
