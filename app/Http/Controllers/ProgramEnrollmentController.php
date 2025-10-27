<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Контроллер для управления записью студентов на программы
 */
class ProgramEnrollmentController extends Controller
{
    /**
     * Записать текущего пользователя на программу
     */
    public function enroll(Program $program)
    {
        $user = Auth::user();
        
        // Проверяем, не записан ли уже пользователь на эту программу
        if ($user->programs()->where('program_id', $program->id)->exists()) {
            return redirect()->back()->with('error', 'Вы уже записаны на эту программу');
        }
        
        // Записываем на программу со статусом 'enrolled'
        $user->programs()->attach($program->id, [
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);
        
        return redirect()->back()->with('success', 'Вы успешно записались на программу!');
    }
    
    /**
     * Отменить запись на программу
     */
    public function unenroll(Program $program)
    {
        $user = Auth::user();
        
        // Проверяем, записан ли пользователь на эту программу
        if (!$user->programs()->where('program_id', $program->id)->exists()) {
            return redirect()->back()->with('error', 'Вы не записаны на эту программу');
        }
        
        // Обновляем статус на 'cancelled'
        $user->programs()->updateExistingPivot($program->id, [
            'status' => 'cancelled',
        ]);
        
        return redirect()->back()->with('success', 'Вы отменили запись на программу');
    }
    
    /**
     * Перевести статус программы в 'active'
     */
    public function activate(Program $program)
    {
        $user = Auth::user();
        
        $user->programs()->updateExistingPivot($program->id, [
            'status' => 'active',
        ]);
        
        return redirect()->back()->with('success', 'Программа активирована');
    }
    
    /**
     * Перевести статус программы в 'completed'
     */
    public function complete(Program $program)
    {
        $user = Auth::user();
        
        $user->programs()->updateExistingPivot($program->id, [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
        
        return redirect()->back()->with('success', 'Программа завершена');
    }
}

