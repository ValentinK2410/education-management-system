<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Program;
use App\Models\Course;

/**
 * Контроллер для управления профилем пользователя
 */
class ProfileController extends Controller
{
    /**
     * Показать профиль пользователя
     */
    public function show($id = null)
    {
        $user = $id ? User::with(['programs', 'courses', 'institutions', 'roles'])->findOrFail($id) : Auth::user();
        
        // Загружаем программы пользователя с подробной информацией
        $enrolledPrograms = $user->programs()->with('institution')->orderBy('pivot_updated_at', 'desc')->get();
        $enrolledCourses = $user->courses()->with('program')->orderBy('pivot_updated_at', 'desc')->get();
        
        // Статистика
        $stats = [
            'total_programs' => $enrolledPrograms->count(),
            'completed_programs' => $enrolledPrograms->where('pivot.status', 'completed')->count(),
            'active_programs' => $enrolledPrograms->where('pivot.status', 'active')->count(),
            'total_courses' => $enrolledCourses->count(),
            'completed_courses' => $enrolledCourses->where('pivot.status', 'completed')->count(),
        ];
        
        return view('profile.show', compact('user', 'enrolledPrograms', 'enrolledCourses', 'stats'));
    }
    
    /**
     * Показать форму редактирования профиля
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }
    
    /**
     * Обновить профиль пользователя
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:100',
            'church' => 'nullable|string|max:255',
            'marital_status' => 'nullable|string|max:50',
            'education' => 'nullable|string|max:255',
            'about_me' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        // Обработка загрузки фото
        if ($request->hasFile('photo')) {
            // Удаляем старое фото, если есть
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            
            $path = $request->file('photo')->store('avatars', 'public');
            $validated['photo'] = $path;
            \Log::info('Photo uploaded for user', ['user_id' => $user->id, 'path' => $path]);
        }
        
        $user->update($validated);
        
        return redirect()->route('profile.show')->with('success', 'Профиль успешно обновлен');
    }
}

