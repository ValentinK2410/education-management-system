<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InstitutionController extends Controller
{
    /**
     * Display a listing of institutions.
     */
    public function index()
    {
        $institutions = Institution::with('programs')->paginate(15);
        return view('admin.institutions.index', compact('institutions'));
    }

    /**
     * Show the form for creating a new institution.
     */
    public function create()
    {
        return view('admin.institutions.create');
    }

    /**
     * Store a newly created institution.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('institutions', 'public');
        }

        Institution::create($data);

        return redirect()->route('admin.institutions.index')
            ->with('success', 'Учебное заведение успешно создано.');
    }

    /**
     * Display the specified institution.
     */
    public function show(Institution $institution)
    {
        $institution->load('programs.courses');
        return view('admin.institutions.show', compact('institution'));
    }

    /**
     * Show the form for editing the institution.
     */
    public function edit(Institution $institution)
    {
        return view('admin.institutions.edit', compact('institution'));
    }

    /**
     * Update the specified institution.
     */
    public function update(Request $request, Institution $institution)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($institution->logo) {
                Storage::disk('public')->delete($institution->logo);
            }
            $data['logo'] = $request->file('logo')->store('institutions', 'public');
        }

        $institution->update($data);

        return redirect()->route('admin.institutions.index')
            ->with('success', 'Учебное заведение успешно обновлено.');
    }

    /**
     * Remove the specified institution.
     */
    public function destroy(Institution $institution)
    {
        // Delete logo file
        if ($institution->logo) {
            Storage::disk('public')->delete($institution->logo);
        }

        $institution->delete();

        return redirect()->route('admin.institutions.index')
            ->with('success', 'Учебное заведение успешно удалено.');
    }
}
